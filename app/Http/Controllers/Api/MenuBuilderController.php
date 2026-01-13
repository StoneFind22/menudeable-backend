<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuBuilderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $restaurant = $user->restaurant->load([
            'categories' => function ($query) {
                $query->orderBy('sort_order');
            },
            'categories.products' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'categories.products.variants',
            'categories.products.tags'
        ]);

        return response()->json(['data' => $restaurant]);
    }
}
