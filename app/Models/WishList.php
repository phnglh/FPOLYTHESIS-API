<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishList extends Model
{
    use HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'userId',
        'sku_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sku()
    {
        return $this->belongsTo(Sku::class);
    }
}
