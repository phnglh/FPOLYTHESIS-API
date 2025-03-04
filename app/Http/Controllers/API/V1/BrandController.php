<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\Brands\BrandCollection;
use App\Http\Resources\Brands\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(Request $request)
    {
        $brands = $this->brandService->getBrandsWithPagination($request);

        return response()->json([
            'success' => true,
            'message' => 'Brand list fetched successfully',
            'data' => new BrandCollection($brands),
            'errors' => null,
        ]);
    }

    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);
        return response()->json([
            'success' => true,
            'message' => 'Brand details fetched successfully',
            'data' => new BrandResource($brand),
            'errors' => null,
        ]);
    }

    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->createBrand($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => new BrandResource($brand),
            'errors' => null,
        ], 201);
    }

    public function update(BrandRequest $request, $id)
    {
        $brand = $this->brandService->updateBrand($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => new BrandResource($brand),
            'errors' => null,
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->brandService->deleteBrand($id);

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully',
                'data' => null,
                'errors' => null,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
                'errors' => null,
            ], 404);
        }
    }
}
