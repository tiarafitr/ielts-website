<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'part' => fake()->numberBetween(1, 3),
            'topic' => fake()->word(),
            'prompt' => fake()->sentence(12),
        ];
    }
}
