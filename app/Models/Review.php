<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment'
    ];

    // Liên kết với bảng Users
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Liên kết với bảng Products
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
