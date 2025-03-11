<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Exceptions\ApiException;

class BrandService
{
    /**
     * Lấy danh sách nhãn hàng với phân trang.
     *
     * @param Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBrandsWithPagination(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        $query = Brand::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * Lấy nhãn hàng theo ID.
     *
     * @param int $id
     * @return Brand
     * @throws ApiException
     */
    public function getBrandById(int $id)
    {
        $brand = Brand::with('children')->find($id);

        if (!$brand) {
            throw new ApiException('Không tìm thấy nhãn hàng', 'BRAND_NOT_FOUND', 404);
        }

        return $brand;
    }

    /**
     * Tạo nhãn hàng mới.
     *
     * @param array $data
     * @return Brand
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
     * @return Brand
     * @throws ApiException
     */
    public function updateBrand(int $id, array $data)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            throw new ApiException('Không tìm thấy nhãn hàng để cập nhật', 'BRAND_NOT_FOUND', 404);
        }

        $brand->update($data);

        return $brand;
    }

    /**
     * Xóa nhãn hàng.
     *
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function deleteBrand(int $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            throw new ApiException('Không tìm thấy nhãn hàng để xóa', 'BRAND_NOT_FOUND', 404);
        }

        $brand->delete();

        return true;
    }
}
