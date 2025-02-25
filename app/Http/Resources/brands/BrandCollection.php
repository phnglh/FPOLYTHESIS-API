<?php

namespace App\Http\Resources\Brands;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandCollection extends ResourceCollection
{
    private $metaData;

    public function __construct($resource, $meta = [])
    {
        parent::__construct($resource);
        $this->metaData = $meta;
    }

    public function toArray($request)
    {
        return [
            'type' => 'brands',
            'attributes' => [
                'items' => BrandResource::collection($this->collection),
                'meta' => array_merge([
                    'total' => $this->resource->total(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'last_page' => $this->resource->lastPage(),
                ], $this->metaData),
            ],
        ];
    }
}
