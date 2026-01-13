<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verificar que el producto pertenece al usuario
        $product = $this->route('product');
        return $product->category->restaurant->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
            'has_variants' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'], // Max 2 MB
            'ingredients_list' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*.name' => ['required', 'string', 'max:50'],
            'tags.*.icon' => ['nullable', 'string', 'max:10'],
        ];
    }
}
