<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material' => ['required', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['sometimes', 'integer'],
            'variants.*.talle' => ['nullable', 'string', 'max:50'],
            'variants.*.sku' => ['required', 'string', 'max:50'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.extra_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
