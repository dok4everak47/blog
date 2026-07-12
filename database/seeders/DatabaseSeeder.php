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
        // 1. 创建用户（1 个管理员 + 9 个普通用户）
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $users = User::factory(9)->create();

        // 2. 创建分类
        $categories = \App\Models\Category::factory(6)->create();

        // 3. 创建标签
        $tags = \App\Models\Tag::factory(12)->create();

        // 4. 创建文章（50 篇，大部分已发布）
        $notes = \App\Models\Note::factory(50)
            ->recycle($users)
            ->published()
            ->create()
            ->each(function ($note) use ($tags) {
                $note->tags()->attach($tags->random(rand(0, min(4, $tags->count()))));
            });

        // 少量草稿
        \App\Models\Note::factory(5)
            ->recycle($users)
            ->draft()
            ->create()
            ->each(function ($note) use ($tags) {
                $note->tags()->attach($tags->random(rand(0, 3)));
            });

        // 5. 创建评论（每篇已发布文章 0~5 条评论 + 随机回复)
        $allUsers = User::all();
        foreach ($notes->take(30) as $note) {
            $count = rand(0, 5);
            $comments = \App\Models\Comment::factory($count)
                ->recycle($allUsers)
                ->create(['note_id' => $note->id]);

            // 部分评论带回复
            foreach ($comments->take(rand(0, $count)) as $comment) {
                \App\Models\Comment::factory(rand(0, 2))
                    ->recycle($allUsers)
                    ->reply($comment)
                    ->create();
            }
        }
    }
}
