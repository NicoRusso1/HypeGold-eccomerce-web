<?php

namespace App\Http\Requests\Api\Assistant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssistantChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:500'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ];
    }
}
