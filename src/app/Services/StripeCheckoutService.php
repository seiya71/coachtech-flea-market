<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;

class StripeCheckoutService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Stripe の決済画面を作成し、その URL を返す
     */
    public function createSession(Item $item, User $user, string $paymentMethod): string
    {
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
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
            ],
            'success_url' => route('checkout.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', [], true),
        ]);

        return $session->url;
    }

    /**
     * Stripe から決済の状態を取得する
     */
    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * 決済を全額返金する
     * (完了画面と Webhook の両方から呼ばれても二重に返金しないよう、決済IDごとに1回に限定する)
     */
    public function refund(string $paymentIntentId, string $sessionId): void
    {
        Refund::create(
            ['payment_intent' => $paymentIntentId],
            ['idempotency_key' => 'refund_' . $sessionId]
        );
    }
}
