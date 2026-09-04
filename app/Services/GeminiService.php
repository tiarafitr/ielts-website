<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    protected ?string $apiKey;

    protected string $model;

    protected string $baseUrl;

    protected string $apiRevision;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
        $this->baseUrl = rtrim(config('services.gemini.base_url'), '/');
        $this->apiRevision = config('services.gemini.api_revision');
    }

   
    public function evaluateSpeakingAnswer(string $questionPrompt, string $answerText): array
    {
        if (empty($this->apiKey)) {
            return $this->fallbackEvaluation($answerText, 'GEMINI_API_KEY is not configured; using offline heuristic.');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Api-Revision' => $this->apiRevision,
                ])
                ->post("{$this->baseUrl}/interactions", [
                    'model' => $this->model,
                    'input' => $this->buildPrompt($questionPrompt, $answerText),
                    'generation_config' => ['temperature' => 0.2],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini API returned a non-successful response.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackEvaluation($answerText, 'Gemini API request failed.');
            }

            $parsed = $this->parseModelJson($this->extractText($response->json()));

            if (! $parsed) {
                return $this->fallbackEvaluation($answerText, 'Could not parse Gemini response as JSON.');
            }

            $bandScore = (float) ($parsed['band_score'] ?? 5.0);

            return [
                'band_score' => $bandScore,
                'criteria' => $this->normaliseCriteria($parsed, $bandScore),
                'strengths' => $this->toStringList($parsed['strengths'] ?? []),
                'areas_to_improve' => $this->toStringList($parsed['areas_to_improve'] ?? []),
                'raw_response' => $response->json(),
            ];
        } catch (Throwable $e) {
            Log::error('Gemini API call threw an exception.', ['message' => $e->getMessage()]);

            return $this->fallbackEvaluation($answerText, 'Exception while calling Gemini API: '.$e->getMessage());
        }
    }

    protected function buildPrompt(string $questionPrompt, string $answerText): string
    {
        return <<<PROMPT
        You are an IELTS Speaking examiner. Evaluate the candidate's spoken answer
        (transcribed as text) to the question below, using the IELTS Speaking band
        descriptors (Fluency & Coherence, Lexical Resource, Grammatical Range &
        Accuracy, Pronunciation is not assessable from text so ignore it).

        Question: "{$questionPrompt}"
        Candidate answer: "{$answerText}"

        Respond ONLY with a raw JSON object in this exact shape, with no prose and no code fences:
        {
          "band_score": <number between 1.0 and 9.0, in 0.5 steps>,
          "fluency": <0.5-step band 1.0-9.0>,
          "lexical_resource": <0.5-step band 1.0-9.0>,
          "grammar": <0.5-step band 1.0-9.0>,
          "coherence": <0.5-step band 1.0-9.0>,
          "strengths": ["short point 1", "short point 2"],
          "areas_to_improve": ["short point 1", "short point 2"]
        }
        PROMPT;
    }

    protected function extractText(?array $payload): ?string
    {
        if (! $payload) {
            return null;
        }

        $candidates = [
            data_get($payload, 'output_text'),
            data_get($payload, 'steps.*.content.*.text'),
            data_get($payload, 'outputs.*.text'),
            data_get($payload, 'outputs.*.content.*.text'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }

            if (is_array($candidate)) {
                $texts = array_values(array_filter(Arr::flatten($candidate), 'is_string'));
                if ($texts) {
                    return end($texts);
                }
            }
        }

        return null;
    }

    protected function parseModelJson(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        $decoded = json_decode(trim(preg_replace('/^```json|```$/m', '', $text)), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function toStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $value)));
    }

    protected function normaliseCriteria(array $parsed, float $bandScore): array
    {
        $criteria = [];

        foreach (['fluency', 'lexical_resource', 'grammar', 'coherence'] as $label) {
            $value = $parsed[$label] ?? null;

            if (! is_numeric($value)) {
                $value = $bandScore;
            }

            $criteria[$label] = round(max(1.0, min(9.0, (float) $value)) * 2) / 2;
        }

        return $criteria;
    }
    
    protected function fallbackEvaluation(string $answerText, string $reason): array
    {
        $wordCount = str_word_count($answerText);

        $bandScore = match (true) {
            $wordCount < 20 => 4.0,
            $wordCount < 50 => 5.5,
            $wordCount < 100 => 6.5,
            default => 7.0,
        };

        return [
            'band_score' => $bandScore,
            'criteria' => [
                'fluency' => max(1.0, $bandScore - 0.5),
                'lexical_resource' => $bandScore,
                'grammar' => max(1.0, $bandScore - 0.5),
                'coherence' => min(9.0, $bandScore + 0.5),
            ],
            'strengths' => [
                'The answer is delivered clearly and stays on topic.',
                'The structure flows naturally from idea to idea.',
            ],
            'areas_to_improve' => [
                'This evaluation was generated in offline mode ('.$reason.'). Connect a GEMINI_API_KEY for a detailed AI breakdown.',
            ],
            'raw_response' => null,
        ];
    }
}
