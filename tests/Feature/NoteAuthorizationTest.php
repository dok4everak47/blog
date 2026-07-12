<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 核心业务逻辑测试：授权（IDOR 防护）、草稿可见性、归属、Slug 唯一。
 */
class NoteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // NoteFactory 依赖至少一个分类存在
        Category::create(['name' => '默认分类']);
    }

    /**
     * 用指定用户创建一篇笔记（绕过 fillable，直接赋值 user_id 更安全）
     */
    private function makeNote(User $user, array $attrs = []): Note
    {
        $note = Note::factory()->make($attrs);
        $note->user_id = $user->id;
        $note->save();

        return $note;
    }

    public function test_guest_cannot_access_create_page(): void
    {
        $this->get(route('notes.create'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author);

        $this->put(route('notes.update', $note), [
            'title' => 'x', 'content' => 'y',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_note_with_correct_owner(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('notes.store'), [
            'title' => '我的第一篇文章',
            'content' => '这是内容',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dashboard'));

        $note = Note::where('title', '我的第一篇文章')->first();
        // 关键断言：文章必须归属到当前登录用户
        $this->assertNotNull($note);
        $this->assertEquals($user->id, $note->user_id);
    }

    public function test_published_note_visible_to_guest(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published', 'title' => '公开文章']);

        $this->get(route('notes.show', $note))->assertStatus(200);
    }

    public function test_draft_note_returns_404_for_guest(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'draft', 'title' => '私密草稿']);

        $this->get(route('notes.show', $note))->assertStatus(404);
    }

    public function test_draft_note_visible_to_owner(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'draft', 'title' => '私密草稿']);
        $this->actingAs($author);

        $this->get(route('notes.show', $note))->assertStatus(200);
    }

    public function test_draft_note_returns_404_for_other_user(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'draft', 'title' => '私密草稿']);
        $other = User::factory()->create();
        $this->actingAs($other);

        // 非作者即使已登录也看不到他人草稿
        $this->get(route('notes.show', $note))->assertStatus(404);
    }

    public function test_user_cannot_update_others_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published', 'title' => '原作者的文章']);
        $intruder = User::factory()->create();
        $this->actingAs($intruder);

        // Policy 拒绝 → 403
        $this->put(route('notes.update', $note), [
            'title' => '被篡改的标题',
            'content' => '坏内容',
        ])->assertForbidden();

        // 数据库内容不变
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => '原作者的文章']);
    }

    public function test_owner_can_update_own_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published', 'title' => '原作者的文章']);
        $this->actingAs($author);

        $this->put(route('notes.update', $note), [
            'title' => '修改后的标题',
            'content' => '新内容',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => '修改后的标题']);
    }

    public function test_user_cannot_delete_others_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);
        $intruder = User::factory()->create();
        $this->actingAs($intruder);

        $this->delete(route('notes.destroy', $note))->assertForbidden();
        $this->assertDatabaseHas('notes', ['id' => $note->id]);
    }

    public function test_owner_can_delete_own_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);
        $this->actingAs($author);

        $this->delete(route('notes.destroy', $note))->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_slug_is_generated_and_unique(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('notes.store'), ['title' => 'Hello World', 'content' => '内容', 'status' => 'published']);
        $this->post(route('notes.store'), ['title' => 'Hello World', 'content' => '内容2', 'status' => 'published']);

        $slugs = Note::where('title', 'Hello World')->pluck('slug')->toArray();
        $this->assertContains('hello-world', $slugs);
        $this->assertContains('hello-world-1', $slugs);
    }
}
