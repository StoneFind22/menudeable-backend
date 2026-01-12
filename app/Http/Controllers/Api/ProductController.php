<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Crear un nuevo producto.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Manejo de Imagen
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = $path;
        }

        $product = $this->productService->create($request->user(), $data);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ], 201);
    }

    /**
     * Listar productos por categoría (opcional para el dashboard)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['category_id' => 'required|exists:categories,id']);
        
        // Verificar propiedad de la categoría
        $category = \App\Models\Category::find($request->category_id);
        if ($category->restaurant_id !== $request->user()->restaurant->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $products = $category->products()
            ->with(['tags', 'variants'])
            ->get();

        return response()->json($products);
    }
}