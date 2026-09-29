<?php

namespace App\Http\Requests\Api\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'label' => ['nullable', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:30'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
