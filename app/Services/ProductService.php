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

    public function getAllProduct($perPage = 10)
    {
        $products = Product::with('category', 'brand', 'skus.attribute_values')->withSum('skus', 'stock')->paginate($perPage);
        if (!$products) {
            throw new ApiException('Không lấy được dữ liệu', 404);
        }
        return $products;
    }

    public function getProductById($id)
    {
        $product = Product::with('category', 'brand', 'skus.attribute_values')->withSum('skus', 'stock')->findOrFail($id);
        if (!$product) {
            throw new ApiException('Product Not Found', 404);
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
                $sku->attribute_values()->detach();
            }

            $product->skus()->delete();
            $product->delete();
        });
    }

    public function togglePublish($id)
    {
        $product = Product::findOrFail($id);
        $product->is_published = !$product->is_published;
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

    public function updateProduct(int|string $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::lockForUpdate()->findOrFail($id);

            // Cập nhật thông tin sản phẩm
            $product->update($this->getProductData($data));

            // Xử lý ảnh sản phẩm
            if (!empty($data['image_url']) && $data['image_url'] instanceof UploadedFile) {
                $uploadedImage = $this->imageUploadService->uploadSingle($data['image_url'], true, $product);
                if (!$uploadedImage) {
                    throw new ApiException('Failed to upload product image', 500);
                }
                if ($product->image_url) {
                    try {
                        Storage::disk('s3')->delete($product->image_url);
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old product image', [
                            'product_id' => $product->id,
                            'image_url' => $product->image_url,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                $product->update(['image_url' => $uploadedImage]);
            }

            return $product;
        });
    }

    // Lấy danh sách SKU của sản phẩm
    public function getSkusByProductId($product_id)
    {
        $product = Product::findOrFail($product_id);
        return $product->skus()->with('attribute_values')->get()->map(function ($sku) {
            return [
                'id' => $sku->id,
                'sku' => $sku->sku,
                'price' => $sku->price,
                'stock' => $sku->stock,
                'image_url' => $sku->image_url,
                'attributes' => $sku->attribute_values->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->pivot->attribute_id,
                        'attribute_name' => $attr->attribute->name,
                        'value' => $attr->pivot->value,
                    ];
                }),
                'images' => $sku->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                    ];
                }),
            ];
        });
    }

    // Thêm SKU mới
    public function createSku($product_id, array $data)
    {
        return DB::transaction(function () use ($product_id, $data) {
            $product = Product::findOrFail($product_id);

            // Kiểm tra attributes
            if (empty($data['attributes']) || !is_array($data['attributes'])) {
                throw new ApiException('Attributes are required to create a new SKU', 400);
            }

            // Kiểm tra xem SKU với attributes này đã tồn tại chưa
            $attributesToMatch = collect($data['attributes'])->mapWithKeys(function ($attr) {
                return [$attr['attribute_id'] => $attr['value']];
            })->toArray();

            $existingSku = $product->skus()->with('attribute_values')->get()->first(function ($sku) use ($attributesToMatch) {
                $skuAttributes = $sku->attribute_values->mapWithKeys(function ($attrValue) {
                    return [$attrValue->pivot->attribute_id => $attrValue->pivot->value];
                })->toArray();
                return empty(array_diff_assoc($attributesToMatch, $skuAttributes)) &&
                    empty(array_diff_assoc($skuAttributes, $attributesToMatch));
            });

            if ($existingSku) {
                throw new ApiException('A SKU with these attributes already exists', 400);
            }

            // Tạo SKU mới
            $sku = Sku::create($this->getSkuData($data, $product_id));
            $this->processSkuRelations($sku, $data);

            return [
                'id' => $sku->id,
                'sku' => $sku->sku,
                'price' => $sku->price,
                'stock' => $sku->stock,
                'image_url' => $sku->image_url,
                'attributes' => $sku->attribute_values->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->pivot->attribute_id,
                        'attribute_name' => $attr->attribute->name,
                        'value' => $attr->pivot->value,
                    ];
                }),
                'images' => $sku->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                    ];
                }),
            ];
        });
    }

    // Cập nhật SKU
    public function updateSku($sku_id, array $data)
    {
        return DB::transaction(function () use ($sku_id, $data) {
            $sku = Sku::findOrFail($sku_id);
            $sku->update($this->getSkuData($data, $sku->product_id));
            $this->processSkuRelations($sku, $data);

            return [
                'id' => $sku->id,
                'sku' => $sku->sku,
                'price' => $sku->price,
                'stock' => $sku->stock,
                'image_url' => $sku->image_url,
                'attributes' => $sku->attribute_values->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->pivot->attribute_id,
                        'attribute_name' => $attr->attribute->name,
                        'value' => $attr->pivot->value,
                    ];
                }),
                'images' => $sku->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                    ];
                }),
            ];
        });
    }

    // Xóa SKU
    public function deleteSku($sku_id)
    {
        return DB::transaction(function () use ($sku_id) {
            $sku = Sku::findOrFail($sku_id);

            // Kiểm tra xem SKU có được sử dụng trong đơn hàng không
            $orderCount = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id') // Join với bảng orders
                ->where('order_items.sku_id', $sku->id)
                ->whereIn('orders.status', ['pending', 'processing']) // Kiểm tra status từ bảng orders
                ->count();

            if ($orderCount > 0) {
                throw new ApiException("Cannot delete SKU because it is used in {$orderCount} pending or processing orders.", 400);
            }

            // Xóa ảnh trên S3
            if ($sku->image_url) {
                try {
                    Storage::disk('s3')->delete($sku->image_url);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete SKU image', [
                        'sku_id' => $sku->id,
                        'image_url' => $sku->image_url,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Xóa quan hệ
            $sku->attribute_values()->detach();
            $sku->images()->delete();
            $sku->delete();
        });
    }

    // ------------------------- PRIVATE -------------------------

    private function processSkus(Product $product, array $skus)
    {
        foreach ($skus as $skuData) {
            $sku = Sku::create($this->getSkuData($skuData, $product->id));
            $this->processSkuRelations($sku, $skuData);
        }
    }

    private function processSkuRelations(Sku $sku, array $skuData)
    {
        // Xử lý ảnh SKU
        if (!empty($skuData['image_url'])) {
            $uploadedImages = [];

            if ($skuData['image_url'] instanceof UploadedFile) {
                Log::info('Uploading single SKU image:', ['file' => $skuData['image_url']]);
                $uploadedImage = $this->imageUploadService->uploadSingle($skuData['image_url'], true, $sku);
                if ($uploadedImage) {
                    $uploadedImages[] = $uploadedImage;
                }
            } elseif (is_array($skuData['image_url'])) {
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

            if (!empty($uploadedImages)) {
                $sku->update(['image_url' => $uploadedImages[0]]);
                $sku->images()->delete();
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

    private function getSkuData(array $skuData, int $productId): array
    {
        $stock = $skuData['stock'];
        if (isset($skuData['id'])) {
            $sku = Sku::find($skuData['id']);
            if ($sku) {
                $pendingOrders = DB::table('order_sku')
                    ->where('sku_id', $sku->id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->sum('quantity');
                if ($stock < $pendingOrders) {
                    throw new ApiException("Cannot reduce stock of SKU {$sku->sku} to {$stock}. There are {$pendingOrders} items in pending orders.", 400);
                }
            }
        }

        return [
            'sku' => isset($skuData['sku']) ? $skuData['sku'] : CodeService::generateCode('SKU', $productId),
            'product_id' => $productId,
            'price' => $skuData['price'],
            'stock' => $stock,
            'image_url' => is_string($skuData['image_url'] ?? null) ? $skuData['image_url'] : null,
        ];
    }
}
