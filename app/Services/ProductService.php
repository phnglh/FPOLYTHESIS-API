<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;

class ProductService {
    public function getAllProducts($perPage = 10) {
        return Product::with('skus.attributeValues')->paginate($perPage);

    }

    public function getProductById($id) {
        return Product::with('skus.attributes')->findOrFail($id);
    }

    // public function createProduct(array $data) {
    //     return Product::create($data);
    // }


    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1️⃣ Tạo sản phẩm
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'categoryId' => $data['categoryId'] ?? null,
                'isPublished' => $data['isPublished'] ?? false
            ]);

            // 2️⃣ Thêm các SKU kèm thuộc tính (nếu có)
            if (!empty($data['skus'])) {
                foreach ($data['skus'] as $skuData) {
                    $sku = Sku::create([
                        'sku' => $skuData['sku'],
                        'productId' => $product->id,
                        'price' => $skuData['price'],
                        'stock' => $skuData['stock']
                    ]);

                    // 3️⃣ Gán thuộc tính cho SKU
                    if (!empty($skuData['attributes'])) {
                        foreach ($skuData['attributes'] as $attr) {
                            $attributeValue = AttributeValue::firstOrCreate([
                                'attributeId' => $attr['attributeId'],
                                'value' => $attr['value']
                            ]);
                            $sku->attributeValues()->attach($attributeValue->id, [
                                'attributeId' => $attr['attributeId']
                            ]);
                            // $sku->attributeValues()->attach($attributeValue->id);
                        }
                    }
                }
            }

            return $product->load('skus.attributeValues');
        });
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
