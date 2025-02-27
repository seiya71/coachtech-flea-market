<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザー情報の変更ができる()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image' => 'default_image.png',  // 初期画像
            'postal_code' => '123-4567',  // 初期郵便番号
            'address' => '東京都新宿区1-1-1',  // 初期住所
            'building_name' => 'テストビル101',  // 初期建物名
        ]);
        $this->actingAs($user);

        // プロフィール編集画面にアクセス
        $response = $this->get('/profile_edit');

        // 初期値としてユーザー情報が入力されていることを期待
        $response->assertStatus(200);  // ステータスコード200でアクセス成功を確認
        $response->assertSee($user->profile_image);  // プロフィール画像が表示されていること
        $response->assertSee($user->name);  // ユーザー名が表示されていること
        $response->assertSee($user->postal_code);  // 郵便番号が表示されていること
        $response->assertSee($user->address);  // 住所が表示されていること
        $response->assertSee($user->building_name);  // 建物名が表示されていること
    }
}
