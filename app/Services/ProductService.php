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
    // ------------------------- PUBLIC -------------------------

    /**
     * Tạo mới Product và các mối quan hệ.
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($this->getProductData($data));
            $this->processSkus($product, $data['skus'] ?? []);

            return $product->load('skus.attribute_values');
        });
    }

    /**
     * Cập nhật Product và các mối quan hệ.
     */
    public function updateProduct(int|string $id, array $data)
    {
        $product = Product::findOrFail($id);

        return DB::transaction(function () use ($product, $data) {
            $product->update($this->getProductData($data));

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                ImageUploadService::upload($data['image'], $product);
            }
            $this->syncSkus($product, $data['skus'] ?? []);

            return $product->load('skus.attribute_values');
        });
    }

    public function getAllProduct($perPage = 10)
    {
        $products = Product::with('category', 'brand', 'skus.attribute_values')->withSum('skus', 'stock')->paginate($perPage);
        if (! $products) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        return $products;
    }

    public function getProductById($id)
    {
        $product = Product::with('category', 'brand', 'skus.attribute_values')->findOrFail($id);

        if (! $product) {
            throw new ApiException(
                'Product Not Found',
                404
            );
        }

        return $product;
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

    // ------------------------- PRIVATE -------------------------

    /**
     * Chuẩn hóa dữ liệu Product.
     */
    private function getProductData(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'],
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'is_published' => $data['is_published'] ?? false,
        ];
    }

    /**
     * Xử lý danh sách SKU khi tạo mới.
     */
    private function processSkus(Product $product, array $skus)
    {
        foreach ($skus as $skuData) {
            $sku = Sku::create($this->getSkuData($skuData, $product->id));
            $this->processSkuRelations($sku, $skuData);
        }
    }

    /**
     * Đồng bộ danh sách SKU khi cập nhật.
     */
    private function syncSkus(Product $product, array $skus)
    {
        $inputSkuIds = collect($skus)->pluck('id')->filter()->toArray();
        $product->skus()->whereNotIn('id', $inputSkuIds)->delete();

        foreach ($skus as $skuData) {
            if (! empty($skuData['id'])) {
                $sku = Sku::find($skuData['id']);
                if ($sku) {
                    $sku->update($this->getSkuData($skuData, $product->id));
                }
            } else {
                $sku = Sku::create($this->getSkuData($skuData, $product->id));
            }
            $this->processSkuRelations($sku, $skuData);
        }
    }

    /**
     * Chuẩn hóa dữ liệu SKU.
     */
    private function getSkuData(array $skuData, int $productId): array
    {
        return [
            'sku' => CodeService::generateCode('SKU', $productId),
            'product_id' => $productId,
            'price' => $skuData['price'],
            'stock' => $skuData['stock'],
        ];
    }

    /**
     * Xử lý các quan hệ của SKU: Image, Attributes.
     */
    private function processSkuRelations(Sku $sku, array $skuData)
    {
        // Xử lý hình ảnh (nếu có)
        if (! empty($skuData['image']) && $skuData['image'] instanceof UploadedFile) {
            ImageUploadService::upload($skuData['image'], $sku);
        }

        // Xử lý thuộc tính
        $sku->attribute_values()->detach(); // Xóa hết trước khi thêm lại
        if (! empty($skuData['attributes']) && is_array($skuData['attributes'])) {
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
