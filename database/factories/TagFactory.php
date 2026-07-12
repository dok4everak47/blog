<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'PHP', 'Laravel', 'JavaScript', 'CSS', 'Vue', 'Python',
                'Docker', 'Linux', 'Git', '数据库', '算法', 'AI',
                '效率', '旅行', '摄影', '音乐', '电影', '阅读',
            ]),
        ];
    }
}
