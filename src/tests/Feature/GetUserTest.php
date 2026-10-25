<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;

class GetUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーの情報を見ることができる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get('/profile');

        $response->assertSee($user->name);
        $response->assertSee($user->profile_image);

        $response = $this->get('/profile?tab=selling');

        $response->assertSee($item->name);

        $otherUser = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $otherUser->id,
            'sold' => true,
        ]);

        $purchase = Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_postal_code' => '1234567',
            'shipping_address' => 'サンプルの住所',
            'shipping_building_name' => 'サンプルの建物',
        ]);

        $response = $this->get('/profile?tab=purchased');

        $response->assertSee($item->name);
    }
}
