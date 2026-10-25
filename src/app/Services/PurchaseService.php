<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

/**
 * Stripe の決済結果を購入として記録する
 *
 * 完了画面と Webhook のどちらが先に届いても同じ結果になるように、
 * 決済ID (stripe_session_id) ごとに1回だけ登録する。
 */
class PurchaseService
{
    // 購入が完了した
    const RESULT_PAID = 'paid';
    // コンビニ払いの支払い待ち（商品は確保済み）
    const RESULT_PENDING = 'pending';
    // 他の人が先に購入していたため購入できなかった
    const RESULT_UNAVAILABLE = 'unavailable';

    private $stripe;

    public function __construct(StripeCheckoutService $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * 決済画面での手続きが完了した決済を購入として記録する
     */
    public function fulfill(Session $session): string
    {
        $itemId = (int) ($session->metadata->item_id ?? 0);
        $userId = (int) ($session->metadata->user_id ?? 0);
        $isPaid = in_array($session->payment_status, ['paid', 'no_payment_required'], true);

        $result = DB::transaction(function () use ($session, $itemId, $userId, $isPaid) {
            // 同じ商品の購入処理が同時に走らないように商品をロックする
            $item = Item::lockForUpdate()->findOrFail($itemId);

            $purchase = Purchase::where('stripe_session_id', $session->id)->first();

            // 登録済みの決済（完了画面の再読み込みや Webhook の再送）
            if ($purchase) {
                if ($isPaid && $purchase->isPending()) {
                    $purchase->update(['status' => Purchase::STATUS_PAID]);
                }

                return $purchase->status;
            }

            if (Purchase::where('item_id', $itemId)->exists()) {
                return self::RESULT_UNAVAILABLE;
            }

            $item->update(['sold' => true]);

            Purchase::create(array_merge([
                'user_id' => $userId,
                'item_id' => $itemId,
                'stripe_session_id' => $session->id,
                'status' => $isPaid ? Purchase::STATUS_PAID : Purchase::STATUS_PENDING,
            ], $this->shippingAddress($session, $userId)));

            return $isPaid ? self::RESULT_PAID : self::RESULT_PENDING;
        });

        // 支払い済みなのに商品を渡せない場合は返金する
        if ($result === self::RESULT_UNAVAILABLE && $isPaid) {
            $this->refund($session);
        }

        return $result;
    }

    /**
     * コンビニ払いの支払いが期限切れになった決済の、商品の確保を取り消す
     */
    public function cancel(Session $session): void
    {
        DB::transaction(function () use ($session) {
            $purchase = Purchase::where('stripe_session_id', $session->id)
                ->where('status', Purchase::STATUS_PENDING)
                ->first();

            if (!$purchase) {
                return;
            }

            Item::lockForUpdate()->whereKey($purchase->item_id)->update(['sold' => false]);
            $purchase->delete();
        });
    }

    /**
     * 配送先（購入手続きをした時点の住所を、決済画面の作成時に Stripe に保存してある）
     */
    private function shippingAddress(Session $session, int $userId): array
    {
        $metadata = $session->metadata;

        if (isset($metadata->shipping_address)) {
            return [
                'shipping_postal_code' => $metadata->shipping_postal_code ?? null,
                'shipping_address' => $metadata->shipping_address,
                'shipping_building_name' => ($metadata->shipping_building_name ?? '') ?: null,
            ];
        }

        // 配送先を保存する前に作られた決済は、今登録されている住所（なければプロフィールの住所）を使う
        $address = Address::where('user_id', $userId)->first() ?? User::findOrFail($userId);

        return [
            'shipping_postal_code' => $address->postal_code,
            'shipping_address' => $address->address,
            'shipping_building_name' => $address->building_name,
        ];
    }

    private function refund(Session $session): void
    {
        try {
            $this->stripe->refund($session->payment_intent, $session->id);
        } catch (ApiErrorException $e) {
            // 返金済みなどで失敗した場合は、Stripe のダッシュボードで確認できるように記録だけ残す
            Log::error('Stripe の返金に失敗しました', [
                'session_id' => $session->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
