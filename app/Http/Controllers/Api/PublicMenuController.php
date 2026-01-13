<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicRestaurantResource;
use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;

class PublicMenuController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService
    ) {}

    public function show(string $slug): JsonResponse
    {
        $restaurant = $this->restaurantService->getPublicMenu($slug);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        return response()->json([
            'data' => new PublicRestaurantResource($restaurant)
        ]);
    }
}
