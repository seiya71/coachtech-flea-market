<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Category;
use App\Models\Purchase;
use PHPUnit\Framework\Attributes\Test;

class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 同じ商品の購入は二重に登録できない()
    {
        $item = Item::factory()->create();
        Purchase::factory()->create(['item_id' => $item->id]);

        $this->expectException(QueryException::class);
        Purchase::factory()->create(['item_id' => $item->id]);
    }

    #[Test]
    public function 住所は1人1件までしか登録できない()
    {
        $user = User::factory()->create();
        Address::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Address::factory()->create(['user_id' => $user->id]);
    }

    #[Test]
    public function 同じ商品に同じカテゴリは二重に登録できない()
    {
        $item = Item::factory()->create();
        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        $this->expectException(QueryException::class);
        $item->categories()->attach($category->id);
    }

    #[Test]
    public function 出品時に同じカテゴリが重複して送られても1回だけ登録される()
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['item_image' => 'item_images/test.png']);
        $category = Category::factory()->create();

        $this->post(route('sell'), [
            'item_name' => '腕時計',
            'brand' => 'ブランド',
            'description' => '説明',
            'condition' => '良好',
            'price' => 1000,
            'categories' => [$category->id, $category->id],
        ])->assertRedirect(route('home'));

        $this->assertDatabaseCount('item_category', 1);
    }

    #[Test]
    public function 住所を何度変更しても1件のまま更新される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        foreach (['1丁目', '2丁目'] as $address) {
            $this->post(route('addressEdit', ['itemId' => $item->id]), [
                'postal_code' => '123-4567',
                'address' => $address,
            ]);
        }

        $this->assertSame(1, Address::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'address' => '2丁目']);
    }
}
