<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Services\StripeCheckoutService;
use Stripe\Exception\InvalidRequestException;
use PHPUnit\Framework\Attributes\Test;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    private function itemData(array $overrides = [])
    {
        return array_merge([
            'item_name' => '腕時計',
            'brand' => 'ブランド',
            'description' => '説明',
            'condition' => '良好',
            'price' => 1000,
        ], $overrides);
    }

    #[Test]
    public function 販売価格は50円以上9999999円以下でないと出品できない()
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['item_image' => 'item_images/test.png']);

        $this->post(route('sell'), $this->itemData(['price' => 49]))
            ->assertSessionHasErrors(['price' => '販売価格は50円以上で入力してください']);

        $this->post(route('sell'), $this->itemData(['price' => 10000000]))
            ->assertSessionHasErrors(['price' => '販売価格は9,999,999円以下で入力してください']);

        $this->assertDatabaseCount('items', 0);

        $this->post(route('sell'), $this->itemData(['price' => 50]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('items', ['price' => 50]);
    }

    #[Test]
    public function コンビニ払いは120円から300000円の商品でしか選べない()
    {
        $this->actingAs(User::factory()->create());

        $this->mock(StripeCheckoutService::class)->shouldNotReceive('createSession');

        foreach ([119, 300001] as $price) {
            $item = Item::factory()->create(['price' => $price, 'sold' => false]);

            $this->get(route('purchase', ['itemId' => $item->id]))
                ->assertOk()
                ->assertSee('カード払い')
                ->assertDontSee('コンビニ払い');

            $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'konbini'])
                ->assertSessionHasErrors(['payment_method' => 'この商品ではその支払い方法は使えません']);
        }

        $item = Item::factory()->create(['price' => 120, 'sold' => false]);
        $this->get(route('purchase', ['itemId' => $item->id]))->assertSee('コンビニ払い');
    }

    #[Test]
    public function Stripeでエラーが起きたら購入画面に戻ってメッセージを表示する()
    {
        $this->actingAs(User::factory()->create());
        $item = Item::factory()->create(['sold' => false]);

        $this->mock(StripeCheckoutService::class)
            ->shouldReceive('createSession')
            ->andThrow(InvalidRequestException::factory('amount too small'));

        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card'])
            ->assertRedirect(route('purchase', ['itemId' => $item->id]))
            ->assertSessionHas('error', '決済画面を開けませんでした。時間をおいてもう一度お試しください。');
    }

    #[Test]
    public function 出品時にフォームで送った画像のパスは使われない()
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $uploadedPath = $this->post(route('uploadItemImage'), [
            'item_image' => UploadedFile::fake()->create('test_image.png', 500),
        ])->json()['item_image'];

        $this->post(route('sell'), $this->itemData(['item_image' => 'profile_images/someone.png']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('items', ['item_image' => $uploadedPath]);
        $this->assertDatabaseMissing('items', ['item_image' => 'profile_images/someone.png']);
    }

    #[Test]
    public function 画像をアップロードしていなければフォームで画像のパスを送っても出品できない()
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('sell'), $this->itemData(['item_image' => 'item_images/someone.png']))
            ->assertSessionHasErrors(['item_image' => '商品画像をアップロードしてください']);

        $this->assertDatabaseCount('items', 0);
    }

    #[Test]
    public function プロフィール更新時にフォームで送った画像のパスは使われない()
    {
        $user = User::factory()->create(['profile_image' => 'profile_images/mine.png']);
        $this->actingAs($user);

        $this->post(route('edit'), [
            'name' => 'テスト',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building_name' => 'ビル',
            'profile_image' => 'item_images/someone.png',
        ])->assertSessionHasNoErrors();

        $this->assertSame('profile_images/mine.png', $user->fresh()->profile_image);
    }

    #[Test]
    public function 同じ商品へのいいねは二重に登録できない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $this->expectException(QueryException::class);
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);
    }
}
