<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function コメントができる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $commentText = 'これはテストです';
        $this->post(route('addcomment', ['itemId' => $item->id]), [
            'user_id' => $user->id,
            'content' => $commentText,
        ]);

        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => $commentText,
        ]);

        $this->assertEquals(1, $item->comments()->count());
    }

    /** @test */
    /** @test */
    public function 未ログインでコメントができない()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->post(route('addcomment', $item->id), [
            'content' => 'これはテストです',
        ]);

        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストです',
        ]);

        $response->assertRedirect('/login');
    }


    /** @test */
    public function コメントを入力しないとエラーメッセージがでる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->post(route('addcomment', $item->id), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content' => 'コメントを入力してください']);
    }

    /** @test */
    public function コメントの文字数が多いとエラーメッセージがでる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create(['user_id' => $user->id]);

        $longComment = str_repeat('あ', 256);

        $response = $this->post(route('addcomment', $item->id), [
            'content' => $longComment,
        ]);

        $response->assertSessionHasErrors(['content']);

        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => $longComment,
        ]);
    }

}
