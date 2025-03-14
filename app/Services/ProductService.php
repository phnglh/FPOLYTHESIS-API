<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB; // them moi

class ProductService
{
    public function getAllProducts($perPage = 10)
    {
        $AllProduct = Product::with('category', 'brand', 'skus.attribute_values')->paginate($perPage);

        if (! $AllProduct) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        return $AllProduct;
    }

    public function getProductById($id)
    {
        $ProductById = Product::with('category', 'brand', 'skus.attribute_values')->findOrFail($id);

        if (! $ProductById) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        return $ProductById;
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'is_published' => $data['is_published'] ?? false,
            ]);

            if (! empty($data['skus'])) {
                foreach ($data['skus'] as $skuData) {
                    if (! empty($skuData['id'])) {
                        $sku = Sku::find($skuData['id']);
                        if ($sku) {
                            $sku->update([
                                'sku' => $skuData['sku'],
                                'price' => $skuData['price'],
                                'stock' => $skuData['stock'],
                            ]);
                        } else {
                            $sku = Sku::create([
                                'sku' => $skuData['sku'],
                                'product_id' => $product->id,
                                'price' => $skuData['price'],
                                'stock' => $skuData['stock'],
                            ]);
                        }
                    } else {
                        $sku = Sku::create([
                            'sku' => $skuData['sku'],
                            'product_id' => $product->id,
                            'price' => $skuData['price'],
                            'stock' => $skuData['stock'],
                        ]);
                    }

                    // Xử lý ảnh SKU
                    if (! empty($skuData['image']) && $skuData['image'] instanceof UploadedFile) {
                        ImageUploadService::upload($skuData['image'], $sku);
                    }

                    // Cập nhật attribute_values
                    $sku->attribute_values()->detach();
                    if (! empty($skuData['attributes'])) {
                        foreach ($skuData['attributes'] as $attr) {
                            $attributeValue = AttributeValue::firstOrCreate([
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value'],
                            ]);
                            $sku->attribute_values()->attach($attributeValue->id, [
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value'],
                            ]);
                        }
                    }
                }
            }

            return $product->load('skus.attribute_values');
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
                'is_published' => $data['is_published'] ?? false,
            ]);

            if (! empty($data['productImage']) && $data['productImage'] instanceof UploadedFile) {
                ImageUploadService::upload($data['productImage'], $product);
            }

            $incomingSkuIds = collect($data['sku'] ?? [])
                ->pluck('id')
                ->filter()
                ->toArray();

            if (! empty($incomingSkuIds)) {
                $product->skus()
                    ->whereNotIn('id', $incomingSkuIds)
                    ->each(function ($sku) {
                        $sku->attribute_values()->detach();
                        $sku->delete();
                    });
            }

            if (! empty($data['sku'])) {
                foreach ($data['sku'] as $skuData) {
                    $sku = null;

                    if (! empty($skuData['id'])) {
                        $sku = Sku::find($skuData['id']);
                    }

                    if ($sku) {
                        $sku->update([
                            'sku' => $skuData['sku'],
                            'price' => $skuData['price'],
                            'stock' => $skuData['stock'],
                        ]);
                    } else {
                        $sku = Sku::create([
                            'sku' => $skuData['sku'],
                            'product_id' => $product->id,
                            'price' => $skuData['price'],
                            'stock' => $skuData['stock'],
                        ]);
                    }

                    if (! empty($skuData['image']) && $skuData['image'] instanceof UploadedFile) {
                        ImageUploadService::upload($skuData['image'], $sku);
                    }

                    $sku->attribute_values()->detach();
                    if (! empty($skuData['attributes'])) {
                        foreach ($skuData['attributes'] as $attr) {
                            $attributeValue = AttributeValue::firstOrCreate([
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value'],
                            ]);
                            $sku->attribute_values()->attach($attributeValue->id, [
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value'],
                            ]);
                        }
                    }
                }
            }

            return $product->load('skus.attribute_values', 'skus.images', 'images');
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
        $product->is_published = ! $product->is_published;
        $product->save();

        return $product;
    }
}
