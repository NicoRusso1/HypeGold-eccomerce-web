<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
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
            'search' => ['sometimes', 'string', 'max:100'],
            'category' => ['sometimes', 'string', 'exists:categories,slug'],
            'material' => ['sometimes', 'string', 'max:100'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'sort' => ['sometimes', Rule::in(['recientes', 'precio_asc', 'precio_desc', 'nombre'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:48'],
        ];
    }
}
