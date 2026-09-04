<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpeakingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Always have a Gemini API key configured in tests so the service
        // tries the HTTP call - which we then intercept with Http::fake().
        config(['services.gemini.api_key' => 'test-key']);
    }

    protected function createUser(array $attrs = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Budi Test',
            'email' => 'budi'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ], $attrs));
    }

    protected function geminiFake(float $band = 6.5): void
    {
        // Interactions API response shape: the model's text lives on the last step.
        Http::fake([
            '*generativelanguage.googleapis.com*' => Http::response([
                'id' => 'interaction-test',
                'status' => 'completed',
                'steps' => [
                    [
                        'type' => 'model_output',
                        'content' => [
                            ['type' => 'text', 'text' => json_encode([
                                'band_score' => $band,
                                'strengths' => ['Good use of vocabulary', 'Clear structure'],
                                'areas_to_improve' => ['Work on verb tenses'],
                            ])],
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_it_lists_speaking_questions_publicly(): void
    {
        Question::factory()->create(['part' => 1, 'topic' => 'Hometown']);
        Question::factory()->create(['part' => 2, 'topic' => 'A Trip']);

        $response = $this->getJson('/api/speaking/questions');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'part', 'topic', 'prompt'],
                ],
            ]);
    }

    public function test_it_filters_questions_by_part(): void
    {
        Question::factory()->create(['part' => 1]);
        Question::factory()->create(['part' => 2]);
        Question::factory()->create(['part' => 2]);

        $response = $this->getJson('/api/speaking/questions?part=2');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_speaking_endpoints_require_authentication(): void
    {
        $this->getJson('/api/speaking/attempts')->assertUnauthorized();

        $question = Question::factory()->create();
        $this->postJson('/api/speaking/submit', [
            'question_id' => $question->id,
            'answer_text' => 'A sufficiently long answer that speaks about the topic.',
        ])->assertUnauthorized();
    }

    public function test_it_rejects_submit_with_invalid_payload(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => 999, // doesn't exist
            'answer_text' => 'short', // too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['question_id', 'answer_text']);
    }

    public function test_it_submits_an_answer_for_the_user_and_stores_feedback(): void
    {
        $user = $this->createUser(['name' => 'Budi']);

        $question = Question::factory()->create([
            'part' => 2,
            'topic' => 'A Memorable Trip',
            'prompt' => 'Describe a trip you really enjoyed.',
        ]);

        $this->geminiFake(6.5);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => $question->id,
            'answer_text' => 'Last year I went to Bali with my family and it was an amazing experience.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.feedback.band_score', 6.5)
            ->assertJsonPath('data.feedback.strengths.0', 'Good use of vocabulary')
            ->assertJsonPath('data.attempt.user_name', 'Budi')
            ->assertJsonPath('data.attempt.user_id', $user->id);

        $this->assertDatabaseHas('attempts', [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'user_name' => 'Budi',
        ]);

        $this->assertDatabaseHas('feedbacks', ['band_score' => 6.5]);

        // The request must hit the Interactions endpoint with the key sent as a
        // header (not a query string) and the API revision pinned.
        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/v1beta/interactions')
                && $request->hasHeader('x-goog-api-key', 'test-key')
                && $request->hasHeader('Api-Revision')
                && $request['model'] === config('services.gemini.model')
                && is_string($request['input'])
                && ! str_contains($request->url(), 'key=');
        });
    }

    public function test_submit_falls_back_gracefully_when_gemini_call_fails(): void
    {
        $user = $this->createUser();
        $question = Question::factory()->create();

        Http::fake([
            '*generativelanguage.googleapis.com*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => $question->id,
            'answer_text' => 'This is a reasonably long test answer for the fallback path.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attempt.question_id', $question->id);

        $this->assertDatabaseCount('feedbacks', 1);
    }

    public function test_attempts_are_scoped_per_user(): void
    {
        $alice = $this->createUser(['name' => 'Alice']);
        $bob = $this->createUser(['name' => 'Bob']);

        $this->geminiFake();
        $this->actingAs($alice, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => Question::factory()->create()->id,
            'answer_text' => 'Alice practices her fluency with this long write-up.',
        ])->assertCreated();

        $this->actingAs($bob, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => Question::factory()->create()->id,
            'answer_text' => 'Bob also writes a long answer to submit for a band report.',
        ])->assertCreated();

        // Each user only sees their own single attempt.
        $this->actingAs($alice, 'sanctum')
            ->getJson('/api/speaking/attempts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_name', 'Alice');

        $this->actingAs($bob, 'sanctum')
            ->getJson('/api/speaking/attempts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_name', 'Bob');
    }

    public function test_a_user_cannot_open_someone_elses_attempt(): void
    {
        $alice = $this->createUser();
        $bob = $this->createUser();

        $this->geminiFake();
        $submitJson = $this->actingAs($alice, 'sanctum')->postJson('/api/speaking/submit', [
            'question_id' => Question::factory()->create()->id,
            'answer_text' => 'Alice shares a long, well-developed answer for the report.',
        ])->assertCreated()->json();

        $attemptId = $submitJson['data']['attempt']['id'];

        // Bob is not allowed to read Alice's attempt.
        $this->actingAs($bob, 'sanctum')
            ->getJson("/api/speaking/attempts/{$attemptId}")
            ->assertForbidden();

        // Alice can.
        $this->actingAs($alice, 'sanctum')
            ->getJson("/api/speaking/attempts/{$attemptId}")
            ->assertOk()
            ->assertJsonPath('data.id', $attemptId);
    }
}
