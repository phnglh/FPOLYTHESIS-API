<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\Brands\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(Request $request)
    {
        $brands = $this->brandService->getBrandsWithPagination($request);

        return $this->paginatedResponse(BrandResource::collection($brands));
    }

    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);

        return $this->successResponse(new BrandResource($brand), 'ADD_TO_BRAND_SUCCESS');
    }

    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->createBrand($request->validated());

        return $this->successResponse($brand, 'ADD_TO_ATTRIBUTES_SUCCESS');
    }

    public function update(BrandRequest $request, $id)
    {
        $brand = $this->brandService->updateBrand($id, $request->validated());

        return $this->successResponse($brand, 'UPDATE_TO_ATTRIBUTES_SUCCESS');
    }

    public function destroy($id)
    {
        $this->brandService->deleteBrand($id);

        return $this->successResponse(null, 'DELETE_TO_ATTRIBUTES_SUCCESS');
    }
}
