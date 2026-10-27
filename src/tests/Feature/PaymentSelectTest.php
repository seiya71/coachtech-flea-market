<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use PHPUnit\Framework\Attributes\Test;

class PaymentSelectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 支払い方法を選択したら小計表示も変更される()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create([
            'price' => 5000,
            'sold' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);
        $response->assertSee('選択してください');

        $response = $this->get(route('purchase', [
            'itemId' => $item->id,
            'payment_method' => 'card',
        ]));

        $response->assertStatus(200);
        $response->assertSee('カード払い');
    }

}
