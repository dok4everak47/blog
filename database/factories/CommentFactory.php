<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(rand(3, 12)),
            'user_id' => \App\Models\User::factory(),
            'note_id' => \App\Models\Note::factory(),
            'parent_id' => null,
        ];

    }

    /**
     * 作为回复
     */
    public function reply(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'note_id' => $parent->note_id,
            'parent_id' => $parent->id,
        ]);
    }
}
