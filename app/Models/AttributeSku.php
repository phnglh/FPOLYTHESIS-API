<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeSku extends Model
{
    protected $table = 'attribute_skus';

    protected $fillable = [
        'sku_id',
        'attribute_id',
        'value',
    ];

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'sku_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
