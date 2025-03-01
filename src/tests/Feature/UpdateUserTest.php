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
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image' => 'default_image.png',
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-1-1',
            'building_name' => 'テストビル101',
        ]);
        $this->actingAs($user);

        $response = $this->get('/profile_edit');
        
        $response->assertStatus(200);
        $response->assertSee($user->profile_image);
        $response->assertSee($user->name);
        $response->assertSee($user->postal_code);
        $response->assertSee($user->address);
        $response->assertSee($user->building_name);
    }
}
