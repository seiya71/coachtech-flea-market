<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\ItemCategory;


class PurchaseTest extends TestCase
{
    use RefreshDatabase; // テスト用のデータベースを毎回リセット

    protected function setUp(): void
    {
        parent::setUp();

        // `Session` を事前にモック
        Mockery::close(); // 既存のモックをクリア
        $mockSession = Mockery::mock('overload:Stripe\Checkout\Session');
        $mockSession->shouldReceive('create')->andReturn((object) [
            'url' => route('checkout.success') . '?session_id=test_session_id'
        ]);
    }

    /** @test */
    public function 購入できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create(['sold' => false]);

        $category = Category::factory()->create(['category_name' => 'ファッション']);
        $item->categories()->attach($category->id);

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);

        $response = $this->post(route('checkout', ['itemId' => $item->id]), [
            'payment_method' => 'card',
            'item_id' => $item->id,
            'shipping_address' => '東京都渋谷区1-1-1',
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'test_session_id']));
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
        $this->actingAs($user);

        $item = Item::factory()->create(['sold' => false]);

        $category = Category::factory()->create(['category_name' => 'ファッション']);
        $item->categories()->attach($category->id);

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);

        $response = $this->post(route('checkout', ['itemId' => $item->id]), [
            'payment_method' => 'card',
            'item_id' => $item->id,
            'shipping_address' => '東京都渋谷区1-1-1',
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'test_session_id']));
        $response->assertStatus(200);

        $response = $this->get('/?tab=all');
        $response->assertStatus(200);

        $response->assertSee('Sold');
    }

    /** @test */
    public function 購入した商品プロフィールに表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create(['sold' => false]);

        $category = Category::factory()->create(['category_name' => 'ファッション']);
        $item->categories()->attach($category->id);

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);

        $response = $this->post(route('checkout', ['itemId' => $item->id]), [
            'payment_method' => 'card',
            'item_id' => $item->id,
            'shipping_address' => '東京都渋谷区1-1-1',
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'test_session_id']));
        $response->assertStatus(200);

        $response = $this->get('/profile?tab=purchased');

        $response->assertSee($item->name);
    }
}
