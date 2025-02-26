<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandService
{
    /**
     * Lấy danh sách nhãn hàng với phân trang.
     *
     * @param int $perPage
     * @param int $currentPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBrandsWithPagination(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        $query = Brand::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * Lấy nhãn hàng theo ID.
     *
     * @param int $id
     * @return \App\Models\Brand
     */
    public function getBrandById(int $id)
    {
        return Brand::with('children')->findOrFail($id);
    }

    /**
     * Tạo nhãn hàng mới.
     *
     * @param array $data
     * @return \App\Models\Brand
     */
    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    /**
     * Cập nhật nhãn hàng.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Brand
     */
    public function updateBrand(int $id, array $data)
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);

        return $brand;
    }

    /**
     * Xóa nhãn hàng.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBrand(int $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Brand not found');
        }

        $brand->delete();

        return true;
    }
}
