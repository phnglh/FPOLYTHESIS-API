<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandService
{
    /**
     * Lấy danh sách nhãn hàng với phân trang.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBrandsWithPagination(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        $query = Brand::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->query('name').'%');
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * Lấy nhãn hàng theo ID.
     *
     * @return Brand
     *
     * @throws ApiException
     */
    public function getBrandById(int $id)
    {
        $brand = Brand::with('children')->find($id);

        if (! $brand) {
            throw new ApiException('Không tìm thấy nhãn hàng', 'BRAND_NOT_FOUND', 404);
        }

        return $brand;
    }

    /**
     * Tạo nhãn hàng mới.
     *
     * @return Brand
     */
    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    /**
     * Cập nhật nhãn hàng.
     *
     * @return Brand
     *
     * @throws ApiException
     */
    public function updateBrand(int $id, array $data)
    {
        $brand = Brand::find($id);

        if (! $brand) {
            throw new ApiException(
                'Không tìm thấy nhãn hàng để cập nhật',
                'BRAND_NOT_FOUND',
                404
            );
        }

        $brand->update($data);

        return $brand;
    }

    /**
     * Xóa nhãn hàng.
     *
     * @return bool
     *
     * @throws ApiException
     */
    public function deleteBrand(int $id)
    {
        $brand = Brand::find($id);

        if (! $brand) {
            throw new ApiException(
                'Không tìm thấy nhãn hàng để xóa',
                'BRAND_NOT_FOUND',
                404
            );
        }

        $brand->delete();

        return true;
    }
}
