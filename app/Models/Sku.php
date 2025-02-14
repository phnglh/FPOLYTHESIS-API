<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $table = 'Skus';

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
}
