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

        return $this->successResponse(new ProductResource($product), 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $product = $this->productService->updateProduct($id, $request->all());

        return $this->successResponse($product, 'Product updated successfully');
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return $this->successResponse('Product deleted successfully');
    }
}
