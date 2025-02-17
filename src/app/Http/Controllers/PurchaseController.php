<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    public function purchase(Request $request, $itemId)
    {
        $userId = Auth::id();

        if (!$userId) {
            return view('auth.login');
        }

        $item = Item::findOrFail($itemId);
        $user = User::findOrFail($userId);

        $paymentMethod = $request->input('payment_method', '');
        $paymentMethods = [
            'card' => 'カード払い',
            'konbini' => 'コンビニ払い',
        ];

        $selectedPaymentMethod = $paymentMethod ?: '';

        return view('purchase', [
            'item' => $item,
            'user' => $user,
            'selectedPaymentMethod' => $selectedPaymentMethod,
            'paymentMethods' => $paymentMethods,
            'request' => $request,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:card,konbini',
        ]);

        $paymentMethod = $request->input('payment_method');

        Stripe::setApiKey(config('services.stripe.secret'));

        $item = Item::findOrFail($request->item_id);

        session([
            'itemId' => $item->id,
            'userId' => Auth::id(),
        ]);

        $session = Session::create([
            'payment_method_types' => [$paymentMethod],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->item_name,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        $itemId = session('itemId');
        $userId = session('userId');

        if (!$itemId || !$userId) {
            return redirect('/')->with('error', '購入情報が見つかりません。');
        }

        DB::transaction(function () use ($itemId, $userId) {
            $item = Item::findOrFail($itemId);
            $item->update(['sold' => true]);

            Purchase::create([
                'user_id' => $userId,
                'item_id' => $itemId,
            ]);
        });

        return view('checkout.success');
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
