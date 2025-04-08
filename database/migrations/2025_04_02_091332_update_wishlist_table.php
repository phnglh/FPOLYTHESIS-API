<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['sku_id']); // Xóa khóa ngoại cũ (nếu có)
            $table->dropColumn('sku_id'); // Xóa cột cũ
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Thêm cột mới
        });
    }

    public function down()
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
        });
    }
};
