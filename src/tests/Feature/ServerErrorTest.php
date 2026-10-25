<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class ServerErrorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 確認メールを再送できる()
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        $response = $this->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('message', '確認メールを再送しました。');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function 未ログインでログインが必要なページを開くとログイン画面に移動する()
    {
        $item = Item::factory()->create();

        $this->get(route('profile_edit'))->assertRedirect('/login');
        $this->get(route('profile'))->assertRedirect('/login');
        $this->get(route('showSell'))->assertRedirect('/login');
        $this->get(route('address', ['itemId' => $item->id]))->assertRedirect('/login');
        $this->post(route('edit'))->assertRedirect('/login');
        $this->post(route('addressEdit', ['itemId' => $item->id]))->assertRedirect('/login');
        $this->post(route('addlike', $item->id))->assertRedirect('/login');
    }

    /** @test */
    public function 住所変更で郵便番号と住所が空だとエラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $item = Item::factory()->create();

        $response = $this->post(route('addressEdit', ['itemId' => $item->id]), [
            'postal_code' => '',
            'address' => '',
            'building_name' => '',
        ]);

        $response->assertSessionHasErrors([
            'postal_code' => '郵便番号を入力してください',
            'address' => '住所を入力してください',
        ]);
        $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
    }

    /** @test */
    public function 住所変更で建物名は空でもよい()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $item = Item::factory()->create();

        $response = $this->post(route('addressEdit', ['itemId' => $item->id]), [
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-2-3',
            'building_name' => '',
        ]);

        $response->assertRedirect(route('purchase', ['itemId' => $item->id]));
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'building_name' => null,
        ]);
    }

    /** @test */
    public function 商品名とブランド名が255文字を超えるとエラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('sell'), [
            'item_name' => str_repeat('あ', 256),
            'brand' => str_repeat('あ', 256),
            'description' => '説明',
            'item_image' => 'item_images/test.png',
            'condition' => '良好',
            'price' => 1000,
        ]);

        $response->assertSessionHasErrors([
            'item_name' => '商品名は255文字以内で入力してください',
            'brand' => 'ブランド名は255文字以内で入力してください',
        ]);
        $this->assertDatabaseCount('items', 0);
    }

    /** @test */
    public function 存在しない商品へのいいねとコメントは404になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('addlike', 999999))->assertNotFound();
        $this->post(route('addcomment', 999999), ['content' => 'コメント'])->assertNotFound();
    }
}
