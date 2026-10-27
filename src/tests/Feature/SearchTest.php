<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use PHPUnit\Framework\Attributes\Test;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 商品名で部分一致検索ができる()
    {
        $item = Item::factory()->create([
            'item_name' => 'アイテムボックス',
        ]);

        $response = $this->get('/?search=アイテム');

        $response->assertStatus(200)
            ->assertSee('アイテムボックス');
    }

    #[Test]
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
