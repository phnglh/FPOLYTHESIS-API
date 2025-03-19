<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\Products\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function update(ProductRequest $request, $id)
    {
        try {

            $product = $this->productService->updateProduct($id, $request->validated());
            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return $this->successResponse('Product deleted successfully');
    }
}
