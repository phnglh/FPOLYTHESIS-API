<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Http\Resources\Categories\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $categories = $this->categoryService->getCategoriesWithPagination($request);

        return response()->json([
            'success' => true,
            'message' => 'Category list fetched successfully',
            'data' => new CategoryCollection($categories),
            'errors' => null,
        ]);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        return response()->json([
            'success' => true,
            'message' => 'Category details fetched successfully',
            'data' => new CategoryResource($category),
            'errors' => null,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
            'errors' => null,
        ], 201);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
            'errors' => null,
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'data' => null,
                'errors' => null,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'errors' => null,
            ], 404);
        }
    }
}
