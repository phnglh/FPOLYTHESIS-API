<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingMethod::insert([
            [
                'name' => 'Giao thường',
                'price' => 20000,
                'estimated_time' => '3-5 ngày',
                'is_express' => false,
            ],
            [
                'name' => 'Giao nhanh',
                'price' => 50000,
                'estimated_time' => '1-2 ngày',
                'is_express' => true,
            ],
        ]);
    }
}
