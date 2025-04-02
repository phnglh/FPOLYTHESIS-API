<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    // ------------------------- PUBLIC -------------------------

    /**
     * Tạo mới Product và các mối quan hệ.
     */
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
        $product = Product::with('category', 'brand', 'skus.attribute_values')->withSum('skus', 'stock')->findOrFail($id);

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
        return DB::transaction(function () use ($id) {
            $product = Product::findOrFail($id);

            if ($product->image_url) {
                Storage::disk('s3')->delete($product->image_url);
            }

            $skus = $product->skus;
            foreach ($skus as $sku) {
                if ($sku->image_url) {
                    Storage::disk('s3')->delete($sku->image_url);
                }

                $sku->attributes()->detach();
            }

            $product->skus()->delete();

            $product->delete();
        });
    }


    public function togglePublish($id)
    {
        $product = Product::findOrFail($id);
        $product->is_published = ! $product->is_published;
        $product->save();

        return $product;
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($this->getProductData($data));

            $this->processSkus($product, $data['skus'] ?? []);

            $product->loadSum('skus', 'stock');

            if (!empty($data['image_url']) && $data['image_url'] instanceof UploadedFile) {
                $uploadedImage = $this->imageUploadService->uploadSingle($data['image_url'], true, $product);

                if ($uploadedImage) {
                    $product->update(['image_url' => $uploadedImage]);
                }
            }


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

            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $uploadedImage = $this->imageUploadService->uploadSingle($data['image'], false);

                if ($uploadedImage) {
                    $product->update(['image_url' => $uploadedImage]);
                }
            }

            $this->syncSkus($product, $data['skus'] ?? []);

            return $product->load('skus.attribute_values');
        });
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
            if (!empty($skuData['id'])) {
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
     * Xử lý các quan hệ của SKU: Image, Attributes.
     */
    private function processSkuRelations(Sku $sku, array $skuData)
    {
        // Xử lý ảnh SKU (cả đơn và nhiều ảnh)
        if (!empty($skuData['image_url'])) {
            $uploadedImages = [];

            if ($skuData['image_url'] instanceof UploadedFile) {
                // Nếu chỉ có một ảnh đơn
                Log::info('Uploading single SKU image:', ['file' => $skuData['image_url']]);
                $uploadedImage = $this->imageUploadService->uploadSingle($skuData['image_url'], true, $sku);
                if ($uploadedImage) {
                    $uploadedImages[] = $uploadedImage;
                }
            } elseif (is_array($skuData['image_url'])) {
                // Nếu là danh sách ảnh
                $validFiles = array_filter($skuData['image_url'], function ($file) {
                    return $file instanceof UploadedFile && $file->isValid();
                });

                if (!empty($validFiles)) {
                    Log::info('Uploading multiple SKU images:', ['files' => $validFiles]);
                    $uploadedImages = $this->imageUploadService->uploadMultiple($validFiles, true, $sku);
                } else {
                    Log::warning('Invalid files detected in SKU images', ['files' => $skuData['image_url']]);
                }
            } else {
                Log::warning('Invalid SKU image format', ['data' => $skuData]);
            }

            // Nếu có ảnh hợp lệ, tiến hành cập nhật vào DB
            if (!empty($uploadedImages)) {
                // Cập nhật ảnh đại diện SKU (ảnh đầu tiên)
                $sku->update(['image_url' => $uploadedImages[0]]);

                // Xóa ảnh cũ trước khi thêm mới
                $sku->images()->delete();

                // Lưu tất cả ảnh vào bảng images
                $imageRecords = array_map(fn ($imgUrl) => ['image_url' => urldecode($imgUrl)], $uploadedImages);
                $sku->images()->createMany($imageRecords);

            }
        }


        // Cập nhật thuộc tính SKU
        $sku->attribute_values()->detach();
        if (!empty($skuData['attributes']) && is_array($skuData['attributes'])) {
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
            'image_url' => is_string($data['image_url'] ?? null) ? $data['image_url'] : null,
        ];
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
            'image_url' => is_string($skuData['image_url'] ?? null) ? $skuData['image_url'] : null,
        ];
    }
}
