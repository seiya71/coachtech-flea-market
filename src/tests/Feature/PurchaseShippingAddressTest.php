<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use App\Services\StripeCheckoutService;
use Stripe\Checkout\Session;

class PurchaseShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    private function stripeSession(Item $item, User $buyer, array $shipping): Session
    {
        return Session::constructFrom([
            'id' => 'test_session_id',
            'status' => 'complete',
            'payment_status' => 'paid',
            'metadata' => array_merge([
                'item_id' => (string) $item->id,
                'user_id' => (string) $buyer->id,
            ], $shipping),
        ]);
    }

    /** @test */
    public function 決済画面を作るときに住所変更画面で変更した配送先がStripeに渡される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);
        Address::factory()->create(['user_id' => $user->id, 'address' => '元の住所']);

        $this->mock(StripeCheckoutService::class)
            ->shouldReceive('createSession')
            ->with(Mockery::any(), Mockery::any(), 'card', Mockery::on(function ($address) {
                return $address->postal_code === '222-2222'
                    && $address->address === '新しい住所'
                    && $address->building_name === '新しい建物';
            }))
            ->once()
            ->andReturn('https://checkout.stripe.com/test');

        $this->actingAs($user);

        $this->post(route('addressEdit', ['itemId' => $item->id]), [
            'postal_code' => '222-2222',
            'address' => '新しい住所',
            'building_name' => '新しい建物',
        ]);

        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card'])
            ->assertRedirect('https://checkout.stripe.com/test');
    }

    /** @test */
    public function 住所を変更しても過去の購入の配送先は変わらない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);
        Address::factory()->create(['user_id' => $user->id]);

        $this->mock(StripeCheckoutService::class)
            ->shouldReceive('retrieveSession')
            ->andReturn($this->stripeSession($item, $user, [
                'shipping_postal_code' => '111-1111',
                'shipping_address' => '購入時の住所',
                'shipping_building_name' => '購入時の建物',
            ]));

        $this->actingAs($user);
        $this->get(route('checkout.success', ['session_id' => 'test_session_id']))->assertOk();

        $this->post(route('addressEdit', ['itemId' => $item->id]), [
            'postal_code' => '999-9999',
            'address' => '引っ越し後の住所',
            'building_name' => '',
        ]);

        $purchase = Purchase::where('item_id', $item->id)->first();
        $this->assertSame('111-1111', $purchase->shipping_postal_code);
        $this->assertSame('購入時の住所', $purchase->shipping_address);
        $this->assertSame('購入時の建物', $purchase->shipping_building_name);
    }

    /** @test */
    public function 住所変更画面の初期値に登録済みの住所とプロフィールの住所が混ざらない()
    {
        $user = User::factory()->create(['building_name' => 'プロフィールの建物']);
        $item = Item::factory()->create(['sold' => false]);
        Address::factory()->create([
            'user_id' => $user->id,
            'address' => '登録済みの住所',
            'building_name' => null,
        ]);

        $this->actingAs($user)->get(route('address', ['itemId' => $item->id]))
            ->assertOk()
            ->assertSee('登録済みの住所')
            ->assertDontSee('プロフィールの建物');
    }

    /** @test */
    public function 配送先を保存する前に作られた決済は今の住所で記録される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);
        Address::factory()->create([
            'user_id' => $user->id,
            'postal_code' => '333-3333',
            'address' => '登録済みの住所',
            'building_name' => null,
        ]);

        $this->mock(StripeCheckoutService::class)
            ->shouldReceive('retrieveSession')
            ->andReturn($this->stripeSession($item, $user, []));

        $this->actingAs($user);
        $this->get(route('checkout.success', ['session_id' => 'test_session_id']))->assertOk();

        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'shipping_postal_code' => '333-3333',
            'shipping_address' => '登録済みの住所',
            'shipping_building_name' => null,
        ]);
    }
}
