<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'discount_value', 'min_order_value',
        'usage_limit', 'used_count', 'start_date', 'end_date', 'is_active',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'discount_value' => 'float',
        'min_order_value' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isValid()
    {
        return $this->is_active
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit)
            && (is_null($this->start_date) || Carbon::now()->greaterThanOrEqualTo($this->start_date))
            && (is_null($this->end_date) || Carbon::now()->lessThanOrEqualTo($this->end_date));
    }
}
