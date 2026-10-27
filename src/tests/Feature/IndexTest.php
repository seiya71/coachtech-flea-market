<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 商品一覧ページで全商品が表示される()
    {
        Item::factory()->count(10)->create();

        $response = $this->get('/?tab=all');

        $response->assertViewHas('items');

        $items = $response->viewData('items');
        $this->assertCount(10, $items->items());
    }

    #[Test]
    public function 売れた商品がsoldとして表示される()
    {
        Item::factory()->create([
            'sold' => true,
        ]);

        $response = $this->get('/?tab=all');

        $response->assertViewHas('items');

        $response->assertSee('Sold');
    }

    #[Test]
    public function ログインユーザーが出品したアイテムは全件取得タブで表示されない()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // ランダムな単語だとページ内の別の文字列（buttonなど）に一致することがあるため固定する
        $item = Item::factory()->create([
            'user_id' => $user->id,
            'item_name' => '自分が出品したテスト用商品',
            'sold' => true,
        ]);

        $response = $this->get('/?tab=all');

        $response->assertDontSee($item->item_name);
    }

    #[Test]
    public function ページのタイトルにアプリ名が表示される()
    {
        $response = $this->get('/?tab=all');

        $response->assertSee('<title>COACHTECHフリマ</title>', false);
    }
}
