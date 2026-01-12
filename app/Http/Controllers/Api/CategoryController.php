<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Listar categorías del restaurante del usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $request->user()
            ->restaurant
            ->categories()
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Crear nueva categoría.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;
        
        // Calcular el siguiente orden
        $maxOrder = $restaurant->categories()->max('sort_order') ?? -1;

        $category = $restaurant->categories()->create([
            'name' => $request->name,
            'sort_order' => $maxOrder + 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($category, 201);
    }

    /**
     * Actualizar categoría.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        // Verificar propiedad
        if ($category->restaurant_id !== $request->user()->restaurant->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean'
        ]);

        $category->update($request->only(['name', 'is_active']));

        return response()->json($category);
    }

    /**
     * Eliminar categoría.
     */
    public function destroy(Request $request, Category $category): JsonResponse
    {
        if ($category->restaurant_id !== $request->user()->restaurant->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Falta validar si existen productos si se elimina

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada']);
    }

    /**
     * Reordenar categorías.
     * Recibe: { "orders": [{ "id": 1, "sort_order": 0 }, { "id": 2, "sort_order": 1 }] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer',
            'orders.*.sort_order' => 'required|integer',
        ]);

        $restaurantId = $request->user()->restaurant->id;

        foreach ($request->orders as $order) {
            Category::where('id', $order['id'])
                ->where('restaurant_id', $restaurantId)
                ->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json(['message' => 'Orden actualizado']);
    }
}