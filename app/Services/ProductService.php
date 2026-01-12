<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Crear un producto completo con tags y variantes.
     */
    public function create(User $user, array $data): Product
    {
        return DB::transaction(function () use ($user, $data) {
            $restaurant = $user->restaurant;

            // 1. Crear el Producto Base
            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null, // Manejo de imagen se haría en Controller o Service de Imagen
                'base_price' => $data['has_variants'] ? null : ($data['base_price'] ?? 0),
                'is_available' => $data['is_available'] ?? true,
                'has_variants' => $data['has_variants'] ?? false,
                'ingredients_list' => $data['ingredients_list'] ?? [],
            ]);

            // 2. Gestionar Tags (Creación al vuelo)
            if (isset($data['tags']) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    // Busca o crea el tag en el contexto de ESTE restaurante
                    $tag = Tag::firstOrCreate(
                        [
                            'restaurant_id' => $restaurant->id,
                            'name' => trim($tagName)
                        ],
                        [
                            'color' => '#3B82F6',
                            'icon' => null
                        ]
                    );
                    $tagIds[] = $tag->id;
                }
                $product->tags()->sync($tagIds);
            }

            // 3. Gestionar Variantes
            if ($product->has_variants && isset($data['variants']) && is_array($data['variants'])) {
                $variantsData = [];
                foreach ($data['variants'] as $index => $variant) {
                    $variantsData[] = [
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                        'is_available' => $variant['is_available'] ?? true,
                        'sort_order' => $index,
                    ];
                }
                $product->variants()->createMany($variantsData);
            }

            return $product->load(['tags', 'variants']);
        });
    }
}
