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
}
