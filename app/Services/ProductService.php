<?php

namespace App\Services;

use App\Models\Product;

class ProductService {
    public function getAllProducts($perPage = 10) {
        return Product::with('skus.attributes')->paginate($perPage);
    }

    public function getProductById($id) {
        return Product::with('skus.attributes')->findOrFail($id);
    }

    public function createProduct(array $data) {
        return Product::create($data);
    }

    public function updateProduct($id, array $data) {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function deleteProduct($id) {
        $product = Product::findOrFail($id);
        $product->delete();
    }

    public function togglePublish($id) {
        $product = Product::findOrFail($id);
        $product->is_published = !$product->is_published;
        $product->save();
        return $product;
    }
}
