<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Exceptions\ApiException; // them moi

class CategoryService
{
    /**
     * Lấy danh sách danh mục với phân trang.
     */
    public function getCategoriesWithPagination(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        $query = Category::with('children')->whereNull('parent_id');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * Lấy danh mục theo ID.
     */
    public function getCategoryById($id)
    {
        $category = Category::with('children')->find($id);

        if (!$category) {
            throw new ApiException(
                'Category not found',
                'CATEGORY_NOT_FOUND',
                404
            );
        } // ko tồn tại 

        return $category;
    }

    /**
     * Tạo danh mục mới.
     */
    public function createCategory(array $data)
    {
        try {
            return Category::create($data);
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Cập nhật danh mục.
     */
    public function updateCategory(int $id, array $data)
    {
        $category = Category::find($id);

        if (!$category) {
            throw new ApiException(
                'Category not found',
                'CATEGORY_NOT_FOUND',
                404
            );
        }

        $category->update($data);

        return $category;
    }

    /**
     * Xóa danh mục.
     */
    public function deleteCategory(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            throw new ApiException(
                'Category not found',
                'CATEGORY_NOT_FOUND',
                404
            );
        }

        $category->delete();

        return true;
    }
}
