<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Category;
use App\Models\ItemCategory;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 送付先住所変更画面にて登録した住所が商品購入画面に反映されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create(['sold' => false]);

        $category = Category::factory()->create(['category_name' => 'ファッション']);
        $item->categories()->attach($category->id);

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);

        $response = $this->get(route('address', ['itemId' => $item->id]));
        $response->assertStatus(200);

        $newAddressData = [
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-2-3',
            'building_name' => '新宿ビル101号室'
        ];
        $this->post(route('addressEdit', ['itemId' => $item->id]), $newAddressData)
            ->assertRedirect(route('purchase', ['itemId' => $item->id]));

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);
        $response->assertSee('123-4567');
        $response->assertSee('東京都新宿区1-2-3');
        $response->assertSee('新宿ビル101号室');
    }
}
