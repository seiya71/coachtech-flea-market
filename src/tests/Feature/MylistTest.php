<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;

class MylistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーがいいねした商品はマイリストに表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $like = Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertSee($item->item_name);
    }

    /** @test */
    public function マイリストに表示された売れた商品はsoldとして表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $otherUser->id,
            'sold' => true,
        ]);

        $like = Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertSee($item->item_name);
        $response->assertSee('Sold');
    }

    public function マイリストに自分が出品した商品は表示されない()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200)
            ->assertSee('現在マイリストはありません。');
    }

    /** @test */
    public function 未ログイン状態でマイリストを開くと商品が表示されない()
    {
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200)
            ->assertSee('現在マイリストはありません。');
    }
}
