<?php

namespace Tests\Unit;

use App\Services\GeminiService;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_it_returns_a_deterministic_fallback_when_no_api_key_is_set(): void
    {
        config(['services.gemini.api_key' => null]);

        $service = new GeminiService();

        $result = $service->evaluateSpeakingAnswer(
            'Describe your hometown.',
            'My hometown is a small quiet city near the mountains.'
        );

        $this->assertArrayHasKey('band_score', $result);
        $this->assertArrayHasKey('strengths', $result);
        $this->assertArrayHasKey('areas_to_improve', $result);
        $this->assertIsFloat($result['band_score']);
        $this->assertNull($result['raw_response']);
    }

    public function test_fallback_gives_higher_score_for_longer_answers(): void
    {
        config(['services.gemini.api_key' => null]);

        $service = new GeminiService();

        $short = $service->evaluateSpeakingAnswer('Q', 'Yes I like it.');
        $long = $service->evaluateSpeakingAnswer('Q', str_repeat('This is a fairly detailed sentence about my hobbies and daily life. ', 15));

        $this->assertGreaterThan($short['band_score'], $long['band_score']);
    }
}
