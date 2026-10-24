<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /** @test */
    public function ログインしていないと商品画像をアップロードできない()
    {
        $response = $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $response->assertRedirect('/login');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function 画像以外のファイルは商品画像としてアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hacked";'),
        ]);

        $response->assertSessionHasErrors('item_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function 商品画像は2MBを超えるとアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('large_image.png', 3000),
        ]);

        $response->assertSessionHasErrors('item_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function jpeg形式の商品画像をアップロードできる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('test_image.jpg', 500),
        ]);

        $response->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($response->json()['item_image']);
    }

    /** @test */
    public function ログインしていないとプロフィール画像をアップロードできない()
    {
        $response = $this->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $response->assertRedirect('/login');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function 画像以外のファイルはプロフィール画像としてアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hacked";'),
        ]);

        $response->assertSessionHasErrors('profile_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function プロフィール画像をアップロードできる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile_edit');
        Storage::disk('public')->assertExists(session('profile_image'));
    }
}
