<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('current_page', 1);

        $products = $this->productService->getAllProducts($perPage, $currentPage);

        return response()->json([
            'success' => true,
            'message' => 'Product list fetched successfully',
            'data' => [
                'products' => $products->items(),
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ]
            ],
            'errors' => null,
        ]);
    }

    public function show($id)
    {
        $product = $this->productService->getProductById($id);

        return response()->json([
            'success' => true,
            'message' => 'Product details fetched successfully',
            'data' => $product,
            'errors' => null,
        ]);
    }

    public function store(Request $request)
    {

        $product = $this->productService->createProduct($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
            'errors' => null,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = $this->productService->updateProduct($id, $request->all());
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
            'errors' => null,
        ]);
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'data' => null,
            'errors' => null,
        ], 200);
    }
}
