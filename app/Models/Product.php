<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'Products';

    protected $fillable = [
        'name',
        'categoryId',
        'slug',
        'description',
    ];

    public function skus()
    {
        return $this->hasMany(Sku::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

}
