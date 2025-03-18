<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $table = 'skus';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'image_url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_skus')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    public function attribute_values()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_skus',
            'sku_id',
            'attribute_value_id'
        )->withPivot('attribute_id', 'value');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function attributeSkus()
    {
        return $this->hasMany(AttributeSku::class, 'sku_id');
    }
}