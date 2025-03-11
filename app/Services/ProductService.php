<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function getAllProducts($perPage = 10)
    {
        return Product::with('category', 'brand', 'skus.attribute_values')->paginate($perPage);
    }

    public function getProductById($id)
    {
        return Product::with('category', 'brand', 'skus.attribute_values')->findOrFail($id);
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_published' => $data['is_published'] ?? false
            ]);


            if (!empty($data['product_image']) && $data['product_image'] instanceof UploadedFile) {
                ImageUploadService::upload($data['product_image'], $product);
            }

            if (!empty($data['skus'])) {
                foreach ($data['skus'] as $skuData) {
                    $sku = Sku::create([
                        'sku' => $skuData['sku'],
                        'product_id' => $product->id,
                        'price' => $skuData['price'],
                        'stock' => $skuData['stock']
                    ]);
                    if (!empty($skuData['image']) && $skuData['image'] instanceof UploadedFile) {
                        ImageUploadService::upload($skuData['image'], $sku);
                    }
                    if (!empty($skuData['attributes'])) {
                        foreach ($skuData['attributes'] as $attr) {
                            $attributeValue = AttributeValue::firstOrCreate([
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value']
                            ]);
                            $sku->attribute_values()->attach($attributeValue->id, [
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value']
                            ]);
                        }
                    }
                }
            }

            return $product->load('skus.attribute_values', 'skus.images', 'images');
        });
    }

    public function updateProduct($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);

            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_published' => $data['is_published'] ?? false
            ]);

            if (!empty($data['productImage']) && $data['productImage'] instanceof UploadedFile) {
                ImageUploadService::upload($data['productImage'], $product);
            }

            $incomingSkuIds = collect($data['skus'] ?? [])
                ->pluck('id')
                ->filter()
                ->toArray();

            $product->skus()
                ->whereNotIn('id', $incomingSkuIds)
                ->each(function ($sku) {
                    $sku->attributeValues()->detach();
                    $sku->delete();
                });

            if (!empty($data['skus'])) {
                foreach ($data['skus'] as $skuData) {
                    if (!empty($skuData['id'])) {
                        $sku = Sku::findOrFail($skuData['id']);
                        $sku->update([
                            'sku' => $skuData['sku'],
                            'price' => $skuData['price'],
                            'stock' => $skuData['stock']
                        ]);
                    } else {
                        $sku = Sku::create([
                            'sku' => $skuData['sku'],
                            'productId' => $product->id,
                            'price' => $skuData['price'],
                            'stock' => $skuData['stock']
                        ]);
                    }

                    if (!empty($skuData['image']) && $skuData['image'] instanceof UploadedFile) {
                        ImageUploadService::upload($skuData['image'], $sku);
                    }

                    $sku->attributeValues()->detach();
                    if (!empty($skuData['attributes'])) {
                        foreach ($skuData['attributes'] as $attr) {
                            $attributeValue = AttributeValue::firstOrCreate([
                                'attributeId' => $attr['attributeId'],
                                'value' => $attr['value']
                            ]);
                            $sku->attributeValues()->attach($attributeValue->id, [
                                'attributeId' => $attr['attributeId'],
                                'value' => $attr['value']
                            ]);
                        }
                    }
                }
            }

            return $product->load('skus.attributeValues', 'skus.images', 'images');
        });
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    }

    public function togglePublish($id)
    {
        $product = Product::findOrFail($id);
        $product->is_published = !$product->is_published;
        $product->save();
        return $product;
    }
}
