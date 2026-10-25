<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Item;
use App\Models\User;
use App\Models\Address;
use App\Services\PurchaseService;
use App\Services\StripeCheckoutService;
use Stripe\Exception\ApiErrorException;

class PurchaseController extends Controller
{
    public function purchase(Request $request, $itemId)
    {
        $userId = Auth::id();

        $item = Item::findOrFail($itemId);
        $user = User::findOrFail($userId);

        if ($error = $this->purchaseError($item)) {
            return redirect()->route('items.show', $item->id)->with('error', $error);
        }

        $address = $this->shippingAddress($user);

        $paymentMethod = $request->input('payment_method', '');
        $paymentMethods = $this->paymentMethods($item);

        return view('purchase', [
            'item' => $item,
            'user' => $user,
            'address' => $address,
            'selectedPaymentMethod' => $paymentMethod ?: '',
            'paymentMethods' => $paymentMethods,
            'request' => $request,
        ]);
    }

    public function checkout(Request $request, StripeCheckoutService $stripe, $itemId)
    {
        $item = Item::findOrFail($itemId);

        $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods($item)))],
        ], [
            'payment_method.required' => '支払い方法を選択してください',
            'payment_method.in' => 'この商品ではその支払い方法は使えません',
        ]);

        if ($error = $this->purchaseError($item)) {
            return redirect()->route('items.show', $item->id)->with('error', $error);
        }

        try {
            $url = $stripe->createSession(
                $item,
                Auth::user(),
                $request->input('payment_method'),
                $this->shippingAddress(Auth::user())
            );
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->route('purchase', ['itemId' => $item->id])
                ->with('error', '決済画面を開けませんでした。時間をおいてもう一度お試しください。');
        }

        return redirect($url);
    }

    public function success(Request $request, StripeCheckoutService $stripe, PurchaseService $purchaseService)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect('/')->with('error', '購入情報が見つかりません。');
        }

        try {
            $session = $stripe->retrieveSession($sessionId);
        } catch (ApiErrorException $e) {
            return redirect('/')->with('error', '購入情報が見つかりません。');
        }

        // 決済画面での手続きが完了していない、または他人の決済の場合は購入扱いにしない
        // (コンビニ払いは支払い番号の発行で complete になり、その時点で商品を確保する)
        if ($session->status !== 'complete' || (int) ($session->metadata->user_id ?? 0) !== Auth::id()) {
            return redirect('/')->with('error', '決済が完了していません。');
        }

        $result = $purchaseService->fulfill($session);

        if ($result === PurchaseService::RESULT_UNAVAILABLE) {
            $message = 'この商品はすでに購入されています。';

            if ($session->payment_status === 'paid') {
                $message .= 'お支払いは返金されます。';
            }

            return redirect('/')->with('error', $message);
        }

        return view('checkout.success', [
            'isPending' => $result === PurchaseService::RESULT_PENDING,
        ]);
    }

    /**
     * 配送先（住所変更画面で変更した住所 → 登録済みの住所 → プロフィールの住所 の順に使う）
     */
    private function shippingAddress(User $user)
    {
        $address = session('shipping_address', null);

        if (!$address) {
            $address = Address::where('user_id', $user->id)->latest()->first();
        }

        if (!$address) {
            $address = new Address([
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'building_name' => $user->building_name,
            ]);
        }

        return $address;
    }

    /**
     * 商品で使える支払い方法
     * (Stripe のコンビニ払いは 120円〜300,000円 の決済にしか使えない)
     */
    private function paymentMethods(Item $item)
    {
        $paymentMethods = ['card' => 'カード払い'];

        if ($item->price >= 120 && $item->price <= 300000) {
            $paymentMethods['konbini'] = 'コンビニ払い';
        }

        return $paymentMethods;
    }

        private function purchaseError(Item $item)
    {
        if ($item->sold) {
            return 'この商品は売り切れです。';
        }

        if ($item->user_id === Auth::id()) {
            return '自分が出品した商品は購入できません。';
        }

        return null;
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
