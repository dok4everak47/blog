<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 搜索功能测试：标题/正文匹配、不包含草稿。
 */
class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::create(['name' => '默认分类']);
    }

    private function publish(User $user, string $title, string $content): void
    {
        $note = Note::factory()->make([
            'title' => $title,
            'content' => $content,
            'status' => 'published',
        ]);
        $note->user_id = $user->id;
        $note->save();
    }

    public function test_search_finds_matching_title(): void
    {
        $user = User::factory()->create();
        $this->publish($user, 'Laravel 入门', '这是一篇关于框架的文章');
        $this->publish($user, 'Vue 教程', '前端框架');

        $this->get(route('search', ['q' => 'Laravel']))
            ->assertStatus(200)
            ->assertSee('Laravel 入门')
            ->assertDontSee('Vue 教程');
    }

    public function test_search_finds_matching_content(): void
    {
        $user = User::factory()->create();
        $this->publish($user, '文章A', '包含关键词 数据库优化 的内容');
        $this->publish($user, '文章B', '别的内容');

        $this->get(route('search', ['q' => '数据库优化']))
            ->assertStatus(200)
            ->assertSee('文章A')
            ->assertDontSee('文章B');
    }

    public function test_search_does_not_show_drafts(): void
    {
        $user = User::factory()->create();
        $draft = Note::factory()->make(['title' => '保密草稿', 'content' => '草稿内容', 'status' => 'draft']);
        $draft->user_id = $user->id;
        $draft->save();

        $this->get(route('search', ['q' => '草稿']))
            ->assertStatus(200)
            ->assertDontSee('保密草稿');
    }
}
