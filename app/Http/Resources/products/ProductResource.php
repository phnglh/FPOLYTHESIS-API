<?php

namespace App\Http\Resources\Products;

use App\Http\Resources\Skus\SkuResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $options = [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'stock' => (int) $this->skus_sum_stock,
            'image_url' => $this->image_url,
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand->name ?? null,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name ?? null,
            'skus' => SkuResource::collection($this->whenLoaded('skus')),
            'options' => new ProductOptionResource($this->resource),
        ];
    }
}
