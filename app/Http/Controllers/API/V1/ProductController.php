<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\SkuRequest;
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
        $products = $this->productService->getAllProduct($perPage);
        return $this->paginatedResponse(ProductResource::collection($products), 'Lấy danh sách sản phẩm thành công');
    }

    public function show($id)
    {
        $product = $this->productService->getProductById($id);
        return $this->successResponse(new ProductResource($product), 'Lấy sản phẩm thành công');
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return $this->successResponse(new ProductResource($product), 'Product created successfully');
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());
            return $this->successResponse(new ProductResource($product), 'Product updated successfully');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);
        return $this->successResponse('Product deleted successfully');
    }

    public function getSkus($product_id)
    {
        try {
            $skus = $this->productService->getSkusByProductId($product_id);
            return $this->successResponse($skus, 'SKUs retrieved successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function createSku(SkuRequest $request, $product_id)
    {
        try {
            $sku = $this->productService->createSku($product_id, $request->validated());
            return $this->successResponse($sku, 'SKU created successfully', 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function updateSku(SkuRequest $request, $sku_id)
    {
        try {
            $sku = $this->productService->updateSku($sku_id, $request->validated());
            return $this->successResponse($sku, 'SKU updated successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function deleteSku($sku_id)
    {
        try {
            $this->productService->deleteSku($sku_id);
            return $this->successResponse(null, 'SKU deleted successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
}
