<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use PHPUnit\Framework\Attributes\Test;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function メール認証が済んでいない人はログインしたまま認証画面に移動し確認メールを再送できる()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('verification.notice'))->assertOk();
        $this->post(route('verification.send'))->assertSessionHas('message', '確認メールを再送しました。');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function ログインに5回失敗すると正しいパスワードでもしばらくログインできない()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrongpassword']);
        }

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('ログインの試行回数が多すぎます。', session('errors')->first('email'));
        $this->assertGuest();
    }

    #[Test]
    public function ログインに成功すると失敗の回数がリセットされる()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrongpassword']);
        }
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'password123'])->assertRedirect('/');
        $this->post('/logout');

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrongpassword']);
        }
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'password123'])->assertRedirect('/');
    }

    #[Test]
    public function 登録されていないメールアドレスとパスワード間違いで同じメッセージが表示される()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'password123'])
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);

        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrongpassword'])
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    #[Test]
    public function メール認証が済んでいない人は出品やいいねなどができない()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);
        $item = Item::factory()->create();

        $notice = route('verification.notice');

        $this->get(route('showSell'))->assertRedirect($notice);
        $this->post(route('sell'))->assertRedirect($notice);
        $this->post(route('addlike', $item->id))->assertRedirect($notice);
        $this->post(route('addcomment', $item->id), ['content' => 'コメント'])->assertRedirect($notice);
        $this->get(route('purchase', ['itemId' => $item->id]))->assertRedirect($notice);
        $this->post(route('checkout', ['itemId' => $item->id]), ['payment_method' => 'card'])->assertRedirect($notice);
        $this->get(route('profile'))->assertRedirect($notice);
        $this->get(route('profile_edit'))->assertRedirect($notice);

        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseCount('comments', 0);
    }
}
