<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
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
            'type' => 'categories',
            'data' => CategoryResource::collection($this->collection),
            'meta' => array_merge([
                'total' => $this->resource->total(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
            ], $this->metaData),
        ];
    }
}
