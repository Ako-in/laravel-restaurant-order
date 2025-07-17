<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // 'id'=>$this->faker()->numberBetween(1,5),
            'name' => $this->faker->randomElement(['ドリンク', 'フード', 'デザート', 'その他']),
        ];
    }
}
