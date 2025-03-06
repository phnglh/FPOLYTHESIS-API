<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'discount_value', 'min_order_value',
        'usage_limit', 'used_count', 'start_date', 'end_date', 'is_active'
    ];

    public function isValid()
    {
        return $this->is_active &&
               ($this->usage_limit === null || $this->used_count < $this->usage_limit) &&
               (now()->between($this->start_date, $this->end_date));
    }
}