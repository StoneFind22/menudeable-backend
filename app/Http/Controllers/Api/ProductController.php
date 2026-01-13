<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Models\Product;
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
     * Actualizar un producto existente.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        // Manejo de Imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = $path;
        }

        $updatedProduct = $this->productService->update($product, $data);

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'product' => $updatedProduct
        ]);
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

    /**
     * Eliminar un producto.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        // Verificar autorización (Ownership)
        if ($product->category->restaurant_id !== $request->user()->restaurant->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $this->productService->delete($product);

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}