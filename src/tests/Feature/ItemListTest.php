<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Database\Factories\CategoryFactory;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品詳細情報が正しく表示される()
    {
        $category = Category::create([
            'category_name' => 'ファッション',
        ]);

        $user = User::factory()->create();

        $item = Item::factory()->create();

        DB::table('likes')->insert([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        DB::table('item_category')->insert([
            'item_id' => $item->id,
            'category_id' => $category->id,
        ]);

        DB::table('comments')->insert([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'この商品はとても素晴らしい！',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('items.show', $item->id));

        $response->assertStatus(200);

        $response->assertSeeText($item->name);
        $response->assertSeeText($item->brand);
        $response->assertSeeText(number_format($item->price));
        $response->assertSeeText($item->likes()->count());
        $response->assertSeeText($item->comments()->count());
        $response->assertSeeText($item->description);
        $response->assertSeeText($category->category_name);
        $response->assertSeeText($item->condition);
        $response->assertSeeText($user->name);
        $response->assertSeeText('この商品はとても素晴らしい！');

    }

    /** @test */
    public function 複数のカテゴリーが表示される()
    {
        $item = Item::factory()->create();

        $category1 = Category::factory()->create(['category_name' => 'カテゴリA']);
        $category2 = Category::factory()->create(['category_name' => 'カテゴリB']);

        $item->categories()->attach([$category1->id, $category2->id]);

        $response = $this->get(route('items.show', $item->id));

        $response->assertSee($category1->category_name);
        $response->assertSee($category2->category_name);
    }
}
