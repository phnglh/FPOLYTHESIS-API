<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'subtotal',
        'discount',
        'final_total',
        'shipping_address',
        'shipping_method_id',
        'shipping_status',
        'coupon_code',
        'notes',
        'ordered_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at'
    ];

    protected $dates = ['ordered_at', 'shipped_at', 'delivered_at', 'cancelled_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }
}
