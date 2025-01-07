<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lấy tất cả danh mục
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return response()->json($categories);
    }

    // Lấy danh mục theo ID
    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);
        return response()->json($category);
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
