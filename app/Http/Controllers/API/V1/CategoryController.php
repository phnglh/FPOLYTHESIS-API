<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\Categories\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

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

        return $this->paginatedResponse(CategoryResource::collection($categories), 'Categories fetched successfully');
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);

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

        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
