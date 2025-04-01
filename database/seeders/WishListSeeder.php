<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WishList;
use App\Models\User;

class WishListSeeder extends Seeder
{
    public function run()
    {
        // Lấy user đầu tiên trong database
        $user = User::first();

        if (!$user) {
            // Nếu chưa có user nào, tạo user mới
            $user = User::create([
                'name' => 'Default User',
                'email' => 'default@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Seed wishlist với user hợp lệ
        WishList::create([
            'user_id' => $user->id, // Dùng ID hợp lệ
            'sku_id' => 101,
        ]);

        WishList::create([
            'user_id' => $user->id,
            'sku_id' => 102,
        ]);
    }
}
