<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品一覧ページで全商品が表示される()
    {
        Item::factory()->count(10)->create();

        $response = $this->get('/?tab=all');

        $response->assertViewHas('items');

        $items = $response->viewData('items');
        $this->assertCount(10, $items->items());
    }

    /** @test */
    public function 売れた商品がsoldとして表示される()
    {
        Item::factory()->create([
            'sold' => true,
        ]);

        $response = $this->get('/?tab=all');

        $response->assertViewHas('items');

        $response->assertSee('Sold');
    }

    /** @test */
    public function ログインユーザーが出品したアイテムは全件取得タブで表示されない()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'sold' => true,
        ]);

        $response = $this->get('/?tab=all');

        $response->assertDontSee($item->item_name);
    }
}
