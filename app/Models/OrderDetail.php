<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'orderId',
        'skuId',
        'productName',
        'quantity',
        'price',
        'totalPrice',
        'productAttributes'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'skuId');
    }
}
