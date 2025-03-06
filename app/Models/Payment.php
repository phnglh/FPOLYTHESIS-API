<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'orderId',
        'paymentMethod',
        'transactionId',
        'amount',
        'status',
        'paidAt'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId');
    }
}