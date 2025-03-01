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

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品にいいねが出来てカウントも増える()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $response = $this->post(route('addlike', $item->id));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(1, $item->likes()->count());
    }

    /** @test */
    public function いいねしたら表示がアイコンの表示が変わる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertSee('☆');

        $this->post(route('addlike', $item->id));

        $response = $this->get(route('addlike', $item->id));

        $response->assertSee('★');
    }

    /** @test */
    public function いいねはも一回押したら減る()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);

        $user->likedItems()->attach($item->id);


        $response = $this->get(route('items.show', $item->id));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $initialLikes = $item->likes()->count();

        $this->post(route('addlike', $item->id));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $item->refresh();
        $this->assertEquals($initialLikes - 1, $item->likes()->count());
    }



}
