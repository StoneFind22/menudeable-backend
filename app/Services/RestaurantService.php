<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RestaurantService
{
    public function __construct(
        protected SlugGenerator $slugGenerator,
        protected QRCodeGenerator $qrGenerator
    ) {}

    public function update(Restaurant $restaurant, array $data): Restaurant
    {
        return DB::transaction(function () use ($restaurant, $data) {
            // 1. Manejo de Logo
            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                if ($restaurant->logo_path) {
                    Storage::disk('public')->delete($restaurant->logo_path);
                }
                $data['logo_path'] = $data['logo']->store('logos', 'public');
            }

            // 2. Actualizar campos
            // Usamos array_key_exists para permitir guardar valores vacíos (borrar datos)
            $restaurant->update([
                'name' => array_key_exists('name', $data) ? $data['name'] : $restaurant->name,
                'country' => array_key_exists('country', $data) ? $data['country'] : $restaurant->country,
                'city' => array_key_exists('city', $data) ? $data['city'] : $restaurant->city,
                'logo_path' => $data['logo_path'] ?? $restaurant->logo_path, // se maneja internamente
                'classification' => array_key_exists('classification', $data) ? $data['classification'] : $restaurant->classification,
                'description' => array_key_exists('description', $data) ? $data['description'] : $restaurant->description,
                'address' => array_key_exists('address', $data) ? $data['address'] : $restaurant->address,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $restaurant->phone,
            ]);

            // 3. Siempre regenerar QR al actualizar perfil para asegurar consistencia
            try {
                // Eliminar QR viejo
                if ($restaurant->qr_path) {
                    Storage::disk('public')->delete($restaurant->qr_path);
                }
                
                $qrPath = $this->qrGenerator->generate($restaurant);
                $restaurant->update(['qr_path' => $qrPath]);
            } catch (\Exception $e) {
                Log::error("Error regenerando QR: " . $e->getMessage());
            }

            return $restaurant;
        });
    }

    public function create(User $user, array $data): Restaurant
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Generar Slug
            $slug = $this->slugGenerator->generate($data['name'], $data['city']);

            // 2. Crear Restaurante (sin QR aún)
            $restaurant = $user->restaurant()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'country' => $data['country'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                // 'classification' y 'logo' se pueden agregar luego si el request lo trae
            ]);

            // 3. Generar QR
            try {
                $qrPath = $this->qrGenerator->generate($restaurant);
                $restaurant->update(['qr_path' => $qrPath]);
            } catch (\Exception $e) {
                Log::error("Error generando QR para restaurante {$restaurant->id}: " . $e->getMessage());
                // No fallamos la transacción por el QR, se puede regenerar luego
            }

            return $restaurant;
        });
    }

    public function getPublicMenu(string $slug): ?Restaurant
    {
        return Restaurant::where('slug', $slug)
            ->with([
                'categories' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'categories.products' => function ($query) {
                    $query->where('is_available', true);
                },
                'categories.products.variants',
                'categories.products.tags'
            ])
            ->first();
    }
}
