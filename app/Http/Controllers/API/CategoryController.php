<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10); 
        $currentPage = $request->query('current_page', 1); 

        $categories = Category::with('children')->whereNull('parent_id')->paginate($perPage, ['*'], 'page', $currentPage);
       
        return response()->json([
            'success' => true,
            'message' => 'Category list fetched successfully',
            // 'data' => [
                'categories' => $categories->items(),
                'meta' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                ],
            // ],
            'errors' => null,
        ]);
    }

    // Lấy danh mục theo ID
    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Category list fetched successfully',
            'data' => [
                'categories' => $categories->items(),
                'pagination' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                ],
            ],
            'errors' => null,
        ]);
    }

    // Tạo danh mục mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories',
            'description' => 'nullable',
            'image_url' => 'nullable|url',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    // Cập nhật danh mục
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:categories,name,' . $id,
            'description' => 'nullable',
            'image_url' => 'nullable|url',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($request->all());
        return response()->json($category);
    }

    // Xóa danh mục
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
