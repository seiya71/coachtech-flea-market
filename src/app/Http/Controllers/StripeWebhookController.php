<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Services\PurchaseService;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Stripe からの決済の通知を受け取る
 *
 * 完了画面を開かずにブラウザを閉じた場合や、コンビニ払いの支払い・期限切れは
 * この通知でしか分からないため、ここでも購入を記録する。
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request, PurchaseService $purchaseService)
    {
        try {
            // Stripe 以外から送られた偽の通知を受け付けないように署名を確認する
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException | SignatureVerificationException $e) {
            return response('Invalid webhook', 400);
        }

        $session = $event->data->object;

        // このアプリ以外の決済や、削除された商品の決済の通知は無視する
        // (200 以外を返すと Stripe が通知を送り直し続けるため)
        $itemId = $session->metadata->item_id ?? null;
        if (!$itemId || !Item::whereKey($itemId)->exists()) {
            return response('Ignored', 200);
        }

        switch ($event->type) {
            // 決済画面での手続きが完了した（カード払いは支払い済み、コンビニ払いは支払い待ち）
            case 'checkout.session.completed':
            // コンビニ払いの支払いが完了した
            case 'checkout.session.async_payment_succeeded':
                $purchaseService->fulfill($session);
                break;

            // コンビニ払いの支払い期限が切れた
            case 'checkout.session.async_payment_failed':
                $purchaseService->cancel($session);
                break;
        }

        return response('OK', 200);
    }
}
