<?php

namespace Database\Factories;

use App\Models\Attempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    public function definition(): array
    {
        $band = fake()->numberBetween(2, 18) / 2;

        return [
            'attempt_id' => Attempt::factory(),
            'band_score' => $band,
            'criteria' => [
                'fluency' => max(1.0, $band - 0.5),
                'lexical_resource' => $band,
                'grammar' => max(1.0, $band - 0.5),
                'coherence' => min(9.0, $band + 0.5),
            ],
            'strengths' => [fake()->sentence(8), fake()->sentence(8)],
            'areas_to_improve' => [fake()->sentence(8)],
            'raw_response' => null,
        ];
    }
}
