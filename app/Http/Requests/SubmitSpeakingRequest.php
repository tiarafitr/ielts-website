<?php

namespace App\Http\Requests;

class SubmitSpeakingRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'answer_text' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.exists' => 'The selected question does not exist.',
            'answer_text.min' => 'The answer is too short to be evaluated meaningfully (minimum 10 characters).',
        ];
    }
}
