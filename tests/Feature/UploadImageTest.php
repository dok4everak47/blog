<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_inline_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('notes.upload-image'), [
                'image' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        $response->assertOk()
            ->assertJsonStructure(['url']);

        $url = $response->json('url');
        $this->assertStringStartsWith('/storage/uploads/', $url);

        // 文件确实写入了 public disk 的 uploads 目录
        Storage::disk('public')->assertExists(ltrim($url, '/storage/'));
    }

    public function test_guests_cannot_upload(): void
    {
        Storage::fake('public');

        $response = $this->postJson(route('notes.upload-image'), [
            'image' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        // web 组的 auth 中间件对未登录用户重定向到登录页（即拦截游客）
        $response->assertRedirect();
    }

    public function test_non_image_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('notes.upload-image'), [
                'image' => UploadedFile::fake()->create('doc.txt', 10),
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['image']]);
    }
}
