<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;

class ItemCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品を出品することができる()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create([
            'category_name' => 'ファッション',
        ]);

        $itemData = [
            'item_name' => 'テスト商品',
            'description' => 'これはテスト商品です。',
            'item_image' => 'storage/item_images/test_image.jpeg',
            'condition' => '新品',
            'price' => 1000,
            'brand' => 'テストブランド',
            'categories' => [1],
        ];

        $response = $this->get('/sell');

        $response = $this->post('/sell', $itemData);

        $this->assertDatabaseHas('items', [
            'item_name' => $itemData['item_name'],
            'description' => $itemData['description'],
            'price' => $itemData['price'],
            'condition' => $itemData['condition'],
            'item_image' => $itemData['item_image'],
            'brand' => $itemData['brand'],
            'user_id' => $user->id,
        ]);

        $response->assertRedirect('/');
    }

}
