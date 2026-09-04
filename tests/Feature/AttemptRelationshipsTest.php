<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_attempt_relationships_resolve_in_both_directions(): void
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();
        $attempt = Attempt::factory()->forUser($user)->create(['question_id' => $question->id]);
        $feedback = Feedback::factory()->create(['attempt_id' => $attempt->id]);

        $this->assertTrue($attempt->user->is($user));
        $this->assertTrue($attempt->question->is($question));
        $this->assertTrue($attempt->feedback->is($feedback));
        $this->assertTrue($feedback->attempt->is($attempt));
        $this->assertTrue($user->attempts->contains($attempt));
        $this->assertTrue($question->attempts->contains($attempt));
    }

    public function test_deleting_a_user_cascades_to_attempts_and_feedback(): void
    {
        $user = User::factory()->create();
        $attempt = Attempt::factory()->forUser($user)->create();
        Feedback::factory()->create(['attempt_id' => $attempt->id]);

        $user->delete();

        $this->assertDatabaseCount('attempts', 0);
        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_an_attempt_cannot_have_two_feedback_rows(): void
    {
        $attempt = Attempt::factory()->create();
        Feedback::factory()->create(['attempt_id' => $attempt->id]);

        $this->expectException(QueryException::class);

        Feedback::factory()->create(['attempt_id' => $attempt->id]);
    }
}
