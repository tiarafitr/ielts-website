<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSpeakingRequest;
use App\Models\Attempt;
use App\Models\Feedback;
use App\Models\Question;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpeakingController extends Controller
{
    public function __construct(protected GeminiService $gemini)
    {
    }

    /**
     * GET /api/speaking/questions — optional ?part=1|2|3 filter.
     */
    public function questions(Request $request): JsonResponse
    {
        $query = Question::query();

        if ($request->filled('part')) {
            $query->where('part', (int) $request->query('part'));
        }

        $questions = $query->orderBy('part')->orderBy('id')->get(['id', 'part', 'topic', 'prompt']);

        return $this->api(['data' => $questions]);
    }

    public function submit(SubmitSpeakingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $question = Question::findOrFail($validated['question_id']);

        $evaluation = $this->gemini->evaluateSpeakingAnswer(
            $question->prompt,
            $validated['answer_text']
        );

        [$attempt, $feedback] = DB::transaction(function () use ($user, $question, $validated, $evaluation) {
            $attempt = Attempt::create([
                'question_id' => $question->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'answer_text' => $validated['answer_text'],
            ]);

            $feedback = Feedback::create([
                'attempt_id' => $attempt->id,
                'band_score' => $evaluation['band_score'],
                'criteria' => $evaluation['criteria'] ?? null,
                'strengths' => $evaluation['strengths'],
                'areas_to_improve' => $evaluation['areas_to_improve'],
                'raw_response' => $evaluation['raw_response'],
            ]);

            return [$attempt, $feedback];
        });

        $attempt->load('question');

        return $this->api([
            'message' => 'Attempt submitted and evaluated successfully.',
            'data' => [
                'attempt' => $attempt,
                'question' => $question,
                'feedback' => $feedback,
            ],
        ], 201);
    }

    /**
     * GET /api/speaking/attempts — only the authenticated user's attempts.
     */
    public function attempts(): JsonResponse
    {
        $attempts = Attempt::with(['question', 'feedback'])
            ->forUser((int) auth()->id())
            ->latest()
            ->get();

        return $this->api(['data' => $attempts]);
    }

    /**
     * GET /api/speaking/attempts/{attempt} — must belong to the caller.
     */
    public function show(Attempt $attempt): JsonResponse
    {
        abort_unless((int) $attempt->user_id === (int) auth()->id(), 403);

        $attempt->load(['question', 'feedback']);

        return $this->api(['data' => $attempt]);
    }
}
