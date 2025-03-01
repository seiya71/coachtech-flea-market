<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        $item = Item::factory()->create([
            'item_name' => 'アイテムボックス',
        ]);

        $response = $this->get('/?search=アイテム');

        $response->assertStatus(200)
            ->assertSee('アイテムボックス');
    }

    /** @test */
    public function 検索状態がマイリストでも保持される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'item_name' => 'アイテムボックス',
        ]);

        $user->likedItems()->attach($item);

        $response = $this->get('/?search=アイテム');

        $response->assertStatus(200)
            ->assertSee('アイテムボックス');

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200)
            ->assertSee('アイテムボックス');
    }

}
