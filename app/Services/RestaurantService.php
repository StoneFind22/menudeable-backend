<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestaurantService
{
    public function __construct(
        protected SlugGenerator $slugGenerator,
        protected QRCodeGenerator $qrGenerator
    ) {}

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
}
