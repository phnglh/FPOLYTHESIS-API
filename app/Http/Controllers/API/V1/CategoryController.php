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

        return $this->paginatedResponse(CategoryResource::collection($categories), 'GET_TO_CATEGORY_SUCCESS');
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);

        return $this->successResponse(new CategoryResource($category), 'GET_TO_CATEGORY_SUCCESS');
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());

        return $this->successResponse(new CategoryResource($category), 'ADD_TO_CATEGORY_SUCCESS', 201);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());

        return $this->successResponse(new CategoryResource($category), 'UPDATE_TO_CATEGORY_SUCCESS');
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);

        return $this->successResponse(null, 'DELETE_TO_CATEGORY_SUCCESS');
    }
}
