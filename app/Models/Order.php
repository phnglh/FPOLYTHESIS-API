<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'shipping_method_id',
        'address_id',
        'order_number',
        'status',
        'payment_status',
        'subtotal',
        'shipping_fee',
        'discount',
        'final_total',
        'notes',
        'ordered_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
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

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
