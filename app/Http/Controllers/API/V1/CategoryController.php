<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Http\Resources\Categories\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends BaseController
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $categories = $this->categoryService->getCategoriesWithPagination($request);
        if ($categories->isEmpty()) {
            return $this->errorResponse('NO_CATEGORIES_FOUND', 'No categories found', 404);
        }
        return $this->paginatedResponse(new CategoryCollection($categories));
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return $this->errorResponse('CATEGORY_NOT_FOUND', 'Category not found', 404);
        }

        return $this->successResponse(new CategoryResource($category), 'Category details fetched successfully');
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());

        return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());

        // dd($category);
        if (!$category) {
            return $this->errorResponse('CATEGORY_NOT_FOUND', 'Category not found', 404);
        }

        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);

            return $this->successResponse(null, 'Category deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('CATEGORY_NOT_FOUND', 'Category not found', 404);
        }
    }
}
