<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurant = $user->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found'], 404);
        }

        // Cargar conteos usando las relaciones definidas en el modelo
        $restaurant->loadCount(['categories', 'products']);
        
        // Calcular "Salud del Perfil"
        $profileScore = 0;
        $checks = [
            'name' => (bool) $restaurant->name,
            'logo' => (bool) $restaurant->logo_path,
            'description' => (bool) $restaurant->description,
            'categories' => $restaurant->categories_count > 0,
            'products' => $restaurant->products_count > 0,
        ];

        foreach ($checks as $check) {
            if ($check) $profileScore += 20;
        }

        return response()->json([
            'stats' => [
                'categories_count' => $restaurant->categories_count,
                'products_count' => $restaurant->products_count,
                'profile_score' => $profileScore,
                'views_count' => 0, // Placeholder para analytics si se despliega xd
            ],
            'restaurant_summary' => [
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'public_url' => $restaurant->public_url,
                'logo_url' => $restaurant->logo_url,
            ]
        ]);
    }
}
