<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_management_routes_are_not_public(): void
    {
        $this->get('/tags')->assertStatus(405);
        $this->get('/tags/create')->assertNotFound();
        $this->get('/tags/not-a-number')->assertNotFound();
    }

    public function test_only_authenticated_users_can_create_tags(): void
    {
        $this->post('/tags', ['name' => '测试标签'])
            ->assertRedirect('/login');

        $this->assertDatabaseMissing('tags', ['name' => '测试标签']);
    }

    public function test_public_tag_page_still_works(): void
    {
        $tag = Tag::create(['name' => '公开标签']);

        $this->get(route('tags.show', $tag))
            ->assertOk()
            ->assertSee('公开标签');
    }
}
