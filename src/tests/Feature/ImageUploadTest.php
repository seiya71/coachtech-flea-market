<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use PHPUnit\Framework\Attributes\Test;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    #[Test]
    public function ログインしていないと商品画像をアップロードできない()
    {
        $response = $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $response->assertRedirect('/login');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    #[Test]
    public function 画像以外のファイルは商品画像としてアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hacked";'),
        ]);

        $response->assertSessionHasErrors('item_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    #[Test]
    public function 商品画像は2MBを超えるとアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('large_image.png', 3000),
        ]);

        $response->assertSessionHasErrors('item_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    #[Test]
    public function jpeg形式の商品画像をアップロードできる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('test_image.jpg', 500),
        ]);

        $response->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($response->json()['item_image']);
    }

    #[Test]
    public function ログインしていないとプロフィール画像をアップロードできない()
    {
        $response = $this->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->create('test_image.png', 500),
        ]);

        $response->assertRedirect('/login');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    #[Test]
    public function 画像以外のファイルはプロフィール画像としてアップロードできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hacked";'),
        ]);

        $response->assertSessionHasErrors('profile_image');
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    #[Test]
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

    #[Test]
    public function 商品画像を選び直すと前に選んでいた画像は削除される()
    {
        $this->actingAs(User::factory()->create());

        $first = $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('first.png', 500),
        ])->json()['item_image'];

        $second = $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('second.png', 500),
        ])->json()['item_image'];

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    #[Test]
    public function 前に選んでいた画像が商品に使われていれば削除しない()
    {
        $this->actingAs(User::factory()->create());

        $first = $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('first.png', 500),
        ])->json()['item_image'];

        Item::factory()->create(['item_image' => $first]);

        $this->post('/upload-item-image', [
            'item_image' => UploadedFile::fake()->create('second.png', 500),
        ]);

        Storage::disk('public')->assertExists($first);
    }

    #[Test]
    public function プロフィール画像を選び直すと前に選んでいた画像は削除される()
    {
        $this->actingAs(User::factory()->create());

        $this->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->create('first.png', 500),
        ]);
        $first = session('profile_image');

        $this->post('/profile_edit/image', [
            'profile_image' => UploadedFile::fake()->create('second.png', 500),
        ]);

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists(session('profile_image'));
    }
}
