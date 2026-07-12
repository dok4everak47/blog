<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1.先建分类
        $categories = ['工作', '日常', '学习'];
        foreach ($categories as $name) {
            \App\Models\Category::create(['name' => $name]);
        }

        // 2.再建标签
        $tagNames = ['工作', '生活', '灵感'];
        foreach ($tagNames as $name) {
            \App\Models\Tag::create(['name' => $name]);
        }

        // 3.建 50 条笔记, 随机给标签
        $tags = \App\Models\Tag::all();

        \App\Models\Note::factory(50)->create()->each(function ($note) use ($tags) {
            $note->tags()->attach($tags->random(rand(1, 3)));
        });
    }
}
