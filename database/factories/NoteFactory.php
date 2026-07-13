<?php

namespace Database\Factories;

use App\Models\Note;
use App\Enums\NoteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6);
        $content = $this->faker->paragraphs(rand(3, 8), true);

        $status = $this->faker->randomElement([NoteStatus::Published, NoteStatus::Published, NoteStatus::Published, NoteStatus::Draft]);

        return [
            'user_id' => \App\Models\User::factory(),
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . $this->faker->unique()->numerify('####'),
            'content' => $content,
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($content), 160),
            'status' => $status,
            'published_at' => $status === NoteStatus::Published ? now() : null,
            'category_id' => \App\Models\Category::inRandomOrder()->first()?->id,
            'cover_image' => null,
            'thumbnail_url' => null,
        ];
    }

    /**
     * 已发布状态
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NoteStatus::Published,
            'published_at' => now(),
        ]);
    }

    /**
     * 草稿状态
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NoteStatus::Draft,
        ]);
    }

    /**
     * 带封面图
     */
    public function withCover(): static
    {
        return $this->state(fn (array $attributes) => [
            'cover_image' => 'covers/' . $this->faker->uuid() . '.jpg',
        ]);
    }
}
