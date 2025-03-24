<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    public function attributeSkus()
    {
        return $this->hasMany(AttributeSku::class);
    }
}
