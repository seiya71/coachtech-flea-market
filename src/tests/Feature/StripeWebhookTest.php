<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Services\StripeCheckoutService;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test';

    private $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);

        $this->stripe = $this->mock(StripeCheckoutService::class);
    }

    /**
     * Stripe と同じ形式で署名した通知を送る
     */
    private function sendWebhook(string $type, Item $item, User $buyer, string $paymentStatus, string $secret = self::WEBHOOK_SECRET)
    {
        $payload = json_encode([
            'id' => 'evt_test',
            'object' => 'event',
            'type' => $type,
            'data' => [
                'object' => [
                    'id' => 'cs_test',
                    'object' => 'checkout.session',
                    'status' => 'complete',
                    'payment_status' => $paymentStatus,
                    'payment_intent' => 'pi_test',
                    'metadata' => [
                        'item_id' => (string) $item->id,
                        'user_id' => (string) $buyer->id,
                        'shipping_postal_code' => '111-1111',
                        'shipping_address' => '購入時の住所',
                        'shipping_building_name' => '',
                    ],
                ],
            ],
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return $this->call('POST', route('stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload);
    }

    /** @test */
    public function 署名が正しくない通知は受け付けない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid', 'whsec_wrong')
            ->assertStatus(400);

        $this->assertDatabaseMissing('purchases', ['item_id' => $item->id]);
        $this->assertFalse($item->fresh()->sold);
    }

    /** @test */
    public function このアプリ以外の決済の通知は無視して200を返す()
    {
        $payload = json_encode([
            'id' => 'evt_test',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_other',
                'object' => 'checkout.session',
                'status' => 'complete',
                'payment_status' => 'paid',
                'metadata' => [],
            ]],
        ]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, self::WEBHOOK_SECRET);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload)->assertOk();

        $this->assertDatabaseCount('purchases', 0);
    }

    /** @test */
    public function 削除された商品の決済の通知は無視して200を返す()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);
        $item->delete();

        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid')->assertOk();

        $this->assertDatabaseCount('purchases', 0);
    }

    /** @test */
    public function 完了画面を開かなくてもカード払いの購入が記録される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid')->assertOk();

        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test',
            'status' => Purchase::STATUS_PAID,
            'shipping_postal_code' => '111-1111',
            'shipping_address' => '購入時の住所',
            'shipping_building_name' => null,
        ]);
        $this->assertTrue($item->fresh()->sold);
    }

    /** @test */
    public function 同じ通知が2回届いても二重に購入されない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid')->assertOk();
        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid')->assertOk();

        $this->assertSame(1, Purchase::where('item_id', $item->id)->count());
    }

    /** @test */
    public function コンビニ払いは支払いが完了したら購入済みになる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'unpaid')->assertOk();

        $this->assertDatabaseHas('purchases', ['stripe_session_id' => 'cs_test', 'status' => Purchase::STATUS_PENDING]);
        $this->assertTrue($item->fresh()->sold);

        $this->sendWebhook('checkout.session.async_payment_succeeded', $item, $user, 'paid')->assertOk();

        $this->assertDatabaseHas('purchases', ['stripe_session_id' => 'cs_test', 'status' => Purchase::STATUS_PAID]);
        $this->assertSame(1, Purchase::where('item_id', $item->id)->count());
    }

    /** @test */
    public function コンビニ払いの支払い期限が切れたら商品が販売中に戻る()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'unpaid')->assertOk();
        $this->sendWebhook('checkout.session.async_payment_failed', $item, $user, 'unpaid')->assertOk();

        $this->assertDatabaseMissing('purchases', ['stripe_session_id' => 'cs_test']);
        $this->assertFalse($item->fresh()->sold);
    }

    /** @test */
    public function 支払い済みの購入は期限切れの通知では取り消されない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->sendWebhook('checkout.session.completed', $item, $user, 'paid')->assertOk();
        $this->sendWebhook('checkout.session.async_payment_failed', $item, $user, 'unpaid')->assertOk();

        $this->assertDatabaseHas('purchases', ['stripe_session_id' => 'cs_test', 'status' => Purchase::STATUS_PAID]);
        $this->assertTrue($item->fresh()->sold);
    }

    /** @test */
    public function 他の人が先に購入していた商品の支払いは返金される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => true]);
        Purchase::factory()->create(['item_id' => $item->id, 'stripe_session_id' => 'other_session_id']);

        $this->stripe->shouldReceive('refund')->with('pi_test', 'cs_test')->once();

        $this->sendWebhook('checkout.session.async_payment_succeeded', $item, $user, 'paid')->assertOk();

        $this->assertDatabaseMissing('purchases', ['stripe_session_id' => 'cs_test']);
    }

    /** @test */
    public function 支払い待ちの決済が他の人に先を越された場合は返金しない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => true]);
        Purchase::factory()->create(['item_id' => $item->id, 'stripe_session_id' => 'other_session_id']);

        $this->stripe->shouldNotReceive('refund');

        $this->sendWebhook('checkout.session.completed', $item, $user, 'unpaid')->assertOk();

        $this->assertDatabaseMissing('purchases', ['stripe_session_id' => 'cs_test']);
    }
}
