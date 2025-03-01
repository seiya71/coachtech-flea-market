<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Address;
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

        $address = session('shipping_address', null);

        if (!$address) {
            $address = Address::where('user_id', $userId)->latest()->first();
        }

        if (!$address) {
            $address = new Address([
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'building_name' => $user->building_name,
            ]);
        }

        $paymentMethod = $request->input('payment_method', '');
        $paymentMethods = [
            'card' => 'カード払い',
            'konbini' => 'コンビニ払い',
        ];

        return view('purchase', [
            'item' => $item,
            'user' => $user,
            'address' => $address,
            'selectedPaymentMethod' => $paymentMethod ?: '',
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
        $itemId = session('itemId', $request->item_id);
        $item = Item::findOrFail($itemId);
        $userId = auth()->id();

        session()->put('itemId', $itemId);
        session()->put('userId', $userId);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => [$paymentMethod === 'card' ? 'card' : 'konbini'],
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
            'success_url' => route('checkout.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', [], true),
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
            $address = Address::where('user_id', $userId)->first();

            if (!$address) {
                $user = User::findOrFail($userId);

                $address = Address::create([
                    'user_id' => $user->id,
                    'postal_code' => $user->postal_code,
                    'address' => $user->address,
                    'building_name' => $user->building_name,
                ]);
            }

            $item = Item::findOrFail($itemId);
            $item->update(['sold' => true]);

            Purchase::create([
                'user_id' => $userId,
                'item_id' => $itemId,
                'address_id' => $address->id,
            ]);
        });

        session()->forget('itemId');

        return view('checkout.success');
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
