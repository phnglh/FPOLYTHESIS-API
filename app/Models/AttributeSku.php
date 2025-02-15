<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeSku extends Model
{
    protected $table = 'attribute_skus';

    protected $fillable = [
        'skuId',
        'attributeId',
        'value',
    ];

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'skuId');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attributeId');
    }
}
