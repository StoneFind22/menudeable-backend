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

            // 2. Gestionar Tags (Creación al vuelo con Icono)
            if (isset($data['tags']) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagData) {
                    if (is_string($tagData)) $tagData = ['name' => $tagData, 'icon' => null];

                    $tag = Tag::updateOrCreate(
                        [
                            'restaurant_id' => $restaurant->id,
                            'name' => trim($tagData['name'])
                        ],
                        [
                            'color' => '#3B82F6',
                            'icon' => $tagData['icon'] ?? null
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

    /**
     * Actualizar un producto existente.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $restaurant = $product->category->restaurant;

            // 1. Actualizar campos básicos
            $product->update([
                'category_id' => $data['category_id'] ?? $product->category_id,
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'image_path' => $data['image_path'] ?? $product->image_path,
                'base_price' => isset($data['has_variants']) && $data['has_variants'] ? null : ($data['base_price'] ?? $product->base_price),
                'is_available' => isset($data['is_available']) ? $data['is_available'] : $product->is_available,
                'has_variants' => $data['has_variants'] ?? $product->has_variants,
                'ingredients_list' => $data['ingredients_list'] ?? $product->ingredients_list,
            ]);

            // 2. Gestionar Tags (Sync con Icono)
            if (isset($data['tags']) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagData) {
                    if (is_string($tagData)) $tagData = ['name' => $tagData, 'icon' => null];

                    $tag = Tag::updateOrCreate(
                        [
                            'restaurant_id' => $restaurant->id,
                            'name' => trim($tagData['name'])
                        ],
                        [
                            'color' => '#3B82F6',
                            'icon' => $tagData['icon'] ?? null
                        ]
                    );
                    $tagIds[] = $tag->id;
                }
                $product->tags()->sync($tagIds);
            }

            // 3. Gestionar Variantes (Por simplicidad, si vienen variantes, borramos y creamos de nuevo o actualizamos)
            if (!$product->has_variants) {
                $product->variants()->delete();
            }

            return $product->load(['tags', 'variants']);
        });
    }

    /**
     * Eliminar un producto y sus recursos asociados.
     */
    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            // 1. Eliminar Imagen
            if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            }

            // 2. Eliminar Relaciones (Tags, Variantes se borran por cascada)
            $product->tags()->detach();
            $product->variants()->delete();

            // 3. Eliminar Producto
            $product->delete();
        });
    }
}
