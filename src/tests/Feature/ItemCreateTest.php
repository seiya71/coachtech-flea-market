<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class ItemCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 商品を出品することができる()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $category = Category::factory()->create();

        $uploadResponse = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $uploadedImagePath = $uploadResponse->json()['item_image'];

        $itemData = [
            'item_name' => 'テスト商品',
            'description' => 'これはテスト商品です。',
            'condition' => '新品',
            'price' => 1000,
            'brand' => 'テストブランド',
            'categories' => [$category->id],
            'item_image' => $uploadedImagePath,
        ];

        $response = $this->actingAs($user)->post('/sell', $itemData);

        $this->assertDatabaseHas('items', [
            'item_name' => $itemData['item_name'],
            'description' => $itemData['description'],
            'price' => $itemData['price'],
            'condition' => $itemData['condition'],
            'item_image' => $uploadedImagePath,
            'brand' => $itemData['brand'],
            'user_id' => $user->id,
        ]);

        $response->assertRedirect('/');
    }

}
