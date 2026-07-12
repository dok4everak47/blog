<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                '技术', '生活', '学习', '工作', '随笔',
                '教程', '开源', '设计', '效率工具', '读书笔记',
            ]),
        ];
    }
}
