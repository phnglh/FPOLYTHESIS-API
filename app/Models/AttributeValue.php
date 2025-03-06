<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['attribute_id', 'value'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function skus()
    {
        return $this->belongsToMany(Sku::class, 'attribute_skus', 'attribute_value_id', 'sku_id')
            ->withPivot('attribute_id', 'value');
    }
}
