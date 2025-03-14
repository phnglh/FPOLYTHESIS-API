<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'category_id',
        'brand_id',
        'slug',
        'description',
    ];

    public function skus()
    {
        return $this->hasMany(Sku::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id')->withDefault();
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
