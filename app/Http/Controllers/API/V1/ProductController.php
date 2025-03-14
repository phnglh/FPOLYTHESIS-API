<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Products\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends BaseController
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

        $products = $this->productService->getAllProduct($perPage, $currentPage);

        // return ProductResource::collection($products);
        return $this->paginatedResponse(ProductResource::collection($products), 'Lấy danh sách sản phẩm thành công');
    }

    public function show($id)
    {
        $product = $this->productService->getProductById($id);

        return $this->successResponse(new ProductResource($product), 'Lấy sản phẩm thành công');
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
