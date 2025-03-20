<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'price', 'estimated_time', 'is_express'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
