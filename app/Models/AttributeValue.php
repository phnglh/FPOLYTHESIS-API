<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['attributeId', 'value'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attributeId');
    }

    // public function skus()
    // {
    //     return $this->belongsToMany(Sku::class, 'attribute_skus', 'attributeValueId', 'skuId');
    // }
}
