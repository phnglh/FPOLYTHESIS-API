<?php

namespace App\Services;

use App\Models\Product;

class ProductService {
    public function getProducts(int $perPage = 10, int $currentPage = 1)
    {
        return Product::with('skus')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getProductById(int $id)
    {
        return Product::with('skus')->findOrFail($id);
    }
}
