<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryService
{
    /**
     * Lấy danh sách danh mục với phân trang.
     *
     * @param int $perPage
     * @param int $currentPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCategoriesWithPagination(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        $query = Category::with('children')->whereNull('parentId');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * Lấy danh mục theo ID.
     *
     * @param int $id
     * @return \App\Models\Category
     */
    public function getCategoryById($id)
    {
        try {
            return Category::with('children')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return ["success" => false, "message" => "Category not found",];
        }
    }

    /**
     * Tạo danh mục mới.
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    /**
     * Cập nhật danh mục.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Category
     */
    public function updateCategory(int $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    /**
     * Xóa danh mục.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        $category->delete();

        return true;
    }
}
