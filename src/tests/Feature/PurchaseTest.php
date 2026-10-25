<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Purchase;
use App\Services\StripeCheckoutService;
use Stripe\Checkout\Session;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = $this->mock(StripeCheckoutService::class);
        $this->stripe->shouldReceive('createSession')->andReturn(
            route('checkout.success') . '?session_id=test_session_id'
        );
    }

    /**
     * Stripe から返ってくる決済情報を作る
     */
    private function stripeSession(Item $item, User $buyer, string $status = 'complete', string $paymentStatus = 'paid'): Session
    {
        return Session::constructFrom([
            'id' => 'test_session_id',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_intent' => 'pi_test',
            'metadata' => [
                'item_id' => (string) $item->id,
                'user_id' => (string) $buyer->id,
            ],
        ]);
    }

    private function purchase(User $buyer, Item $item)
    {
        $this->actingAs($buyer);

        $this->get(route('purchase', ['itemId' => $item->id]))->assertStatus(200);

        $this->post(route('checkout', ['itemId' => $item->id]), [
            'payment_method' => 'card',
        ])->assertRedirect(route('checkout.success') . '?session_id=test_session_id');

        return $this->get(route('checkout.success', ['session_id' => 'test_session_id']));
    }

    /** @test */
    public function 購入できる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $category = Category::factory()->create(['category_name' => 'ファッション']);
        $item->categories()->attach($category->id);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user));

        $response = $this->purchase($user, $item);
        $response->assertStatus(200);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /** @test */
    public function 購入したらSoldと表示される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user));

        $this->purchase($user, $item)->assertStatus(200);

        $response = $this->get('/?tab=all');
        $response->assertStatus(200);

        $response->assertSee('Sold');
    }

    /** @test */
    public function 購入した商品プロフィールに表示される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user));

        $this->purchase($user, $item)->assertStatus(200);

        $response = $this->get('/profile?tab=purchased');

        $response->assertSee($item->item_name);
    }

    /** @test */
    public function 決済が完了していなければ購入されない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user, 'open'));

        $this->purchase($user, $item)->assertRedirect('/');

        $this->assertDatabaseMissing('purchases', ['item_id' => $item->id]);
        $this->assertFalse($item->fresh()->sold);
    }

    /** @test */
    public function session_idがなければ購入されない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldNotReceive('retrieveSession');

        $response = $this->actingAs($user)->get(route('checkout.success'));

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('purchases', ['item_id' => $item->id]);
    }

    /** @test */
    public function 他人の決済情報では購入されない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $otherUser));

        $response = $this->actingAs($user)->get(route('checkout.success', ['session_id' => 'test_session_id']));

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('purchases', ['item_id' => $item->id]);
    }

    /** @test */
    public function 完了画面を再読み込みしても二重に購入されない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user));

        $this->purchase($user, $item)->assertStatus(200);
        $this->get(route('checkout.success', ['session_id' => 'test_session_id']))->assertStatus(200);

        $this->assertSame(1, Purchase::where('item_id', $item->id)->count());
    }

    /** @test */
    public function コンビニ払いは支払い待ちとして商品が確保される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user, 'complete', 'unpaid'));

        $response = $this->purchase($user, $item);
        $response->assertStatus(200);
        $response->assertSee('コンビニでのお支払いが確認できしだい、購入が完了します。');

        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'status' => Purchase::STATUS_PENDING,
        ]);
        $this->assertTrue($item->fresh()->sold);

        $this->get(route('profile', ['tab' => 'purchased']))->assertSee('支払い待ち');
    }

    /** @test */
    public function 他の人が先に購入していた場合は返金される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false]);

        $this->stripe->shouldReceive('retrieveSession')->andReturn($this->stripeSession($item, $user));

        $this->actingAs($user);
        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card']);

        // 決済画面で支払っている間に、他の人が先に購入した
        Purchase::factory()->create(['item_id' => $item->id, 'stripe_session_id' => 'other_session_id']);
        $item->update(['sold' => true]);

        $this->stripe->shouldReceive('refund')->with('pi_test', 'test_session_id')->once();

        $this->get(route('checkout.success', ['session_id' => 'test_session_id']))
            ->assertRedirect('/')
            ->assertSessionHas('error', 'この商品はすでに購入されています。お支払いは返金されます。');

        $this->assertDatabaseMissing('purchases', ['stripe_session_id' => 'test_session_id']);
    }

    /** @test */
    public function 決済画面ではURLの商品が購入対象になる()
    {
        $user = User::factory()->create();
        $itemA = Item::factory()->create(['sold' => false]);
        $itemB = Item::factory()->create(['sold' => false]);

        $this->stripe = $this->mock(StripeCheckoutService::class);
        $this->stripe->shouldReceive('createSession')
            ->with(Mockery::on(function ($item) use ($itemB) {
                return $item->id === $itemB->id;
            }), Mockery::any(), 'card')
            ->once()
            ->andReturn('https://checkout.stripe.com/test');

        $this->actingAs($user)
            ->withSession(['itemId' => $itemA->id])
            ->post(route('checkout', ['itemId' => $itemB->id]), ['payment_method' => 'card'])
            ->assertRedirect('https://checkout.stripe.com/test');
    }

    /** @test */
    public function 売り切れの商品は購入できない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => true]);

        $this->stripe->shouldNotReceive('createSession');

        $this->actingAs($user)->get(route('purchase', ['itemId' => $item->id]))
            ->assertRedirect(route('items.show', $item->id));

        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card'])
            ->assertRedirect(route('items.show', $item->id));
    }

    /** @test */
    public function 自分が出品した商品は購入できない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold' => false, 'user_id' => $user->id]);

        $this->stripe->shouldNotReceive('createSession');

        $this->actingAs($user)->get(route('purchase', ['itemId' => $item->id]))
            ->assertRedirect(route('items.show', $item->id));

        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card'])
            ->assertRedirect(route('items.show', $item->id));
    }

    /** @test */
    public function ログインしていないと購入画面に進めない()
    {
        $item = Item::factory()->create(['sold' => false]);

        $this->get(route('purchase', ['itemId' => $item->id]))->assertRedirect('/login');
    }
}
