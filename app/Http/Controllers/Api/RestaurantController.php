<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Restaurant\StoreRestaurantRequest;
use App\Http\Requests\Api\Restaurant\UpdateRestaurantRequest;
use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService
    ) {}

    /**
     * Actualizar el perfil del restaurante.
     */
    public function update(UpdateRestaurantRequest $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;
        
        $updatedRestaurant = $this->restaurantService->update(
            $restaurant,
            $request->validated()
        );

        return response()->json([
            'message' => 'Configuración actualizada correctamente',
            'restaurant' => $updatedRestaurant
        ]);
    }

    /**
     * Crear el perfil del restaurante.
     */
    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $restaurant = $this->restaurantService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Restaurante creado exitosamente',
            'restaurant' => $restaurant
        ], 201);
    }

    /**
     * Mostrar el restaurante del usuario autenticado.
     */
    public function show(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No tienes un restaurante registrado'], 404);
        }

        return response()->json($restaurant);
    }
}