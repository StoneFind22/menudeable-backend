<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificar que la categoría pertenezca al restaurante del usuario
        $category = \App\Models\Category::find($this->category_id);
        return $category && $category->restaurant_id === $this->user()->restaurant->id;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB
            
            'is_available' => ['boolean'],
            'has_variants' => ['boolean'],
            
            // Si NO tiene variantes, el precio base es obligatorio
            'base_price' => ['exclude_if:has_variants,true', 'required', 'numeric', 'min:0'],
            
            // Ingredientes
            'ingredients_list' => ['nullable', 'array'],
            'ingredients_list.*' => ['string'],

            // Tags (Array de nombres)
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            // Variantes (Si has_variants es true)
            'variants' => ['required_if:has_variants,true', 'array', 'min:1'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.is_available' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'base_price.required' => 'El precio es obligatorio si el producto no tiene variantes.',
            'variants.required_if' => 'Debes agregar al menos una variante (ej: Tamaño) si activaste la opción.',
        ];
    }
}
