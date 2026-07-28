<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::create(['name' => '默认分类']);
    }

    private function makeNote(User $user, array $attrs = []): Note
    {
        $note = Note::factory()->make($attrs);
        $note->user_id = $user->id;
        $note->save();

        return $note;
    }

    public function test_guest_cannot_toggle_reaction(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);

        $this->post(route('notes.reactions.toggle', $note))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_like_published_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson(route('notes.reactions.toggle', $note));

        $response->assertOk()
            ->assertJson(['liked' => true, 'count' => 1]);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'note_id' => $note->id,
            'type' => 'like',
        ]);
    }

    public function test_authenticated_user_can_unlike_note(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);
        $user = User::factory()->create();
        Reaction::create([
            'user_id' => $user->id,
            'note_id' => $note->id,
            'type' => 'like',
        ]);

        $this->actingAs($user);
        $response = $this->postJson(route('notes.reactions.toggle', $note));

        $response->assertOk()
            ->assertJson(['liked' => false, 'count' => 0]);

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'note_id' => $note->id,
        ]);
    }

    public function test_draft_note_returns_404_when_toggling_reaction(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'draft']);
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->postJson(route('notes.reactions.toggle', $note))
            ->assertNotFound();
    }

    public function test_show_page_displays_reaction_button_and_count(): void
    {
        $author = User::factory()->create();
        $note = $this->makeNote($author, ['status' => 'published']);
        $user = User::factory()->create();
        Reaction::create([
            'user_id' => $user->id,
            'note_id' => $note->id,
            'type' => 'like',
        ]);

        $response = $this->actingAs($user)->get(route('notes.show', $note));

        $response->assertStatus(200)
            ->assertSee('分享')
            ->assertSee('1');
    }
}
