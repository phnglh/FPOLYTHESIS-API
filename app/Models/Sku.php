<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $table = 'skus';

    protected $fillable = [
        'productId',
        'sku',
        'price',
        'stock',
        'imageUrl',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }


    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_skus')
            ->withPivot('attributeValueId')
            ->withTimestamps();
    }
    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_skus',
            'skuId',
            'attributeValueId'
        )->withPivot('attributeId', 'value')->as('attribute_skus');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
