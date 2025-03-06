<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('userId')->constrained('users')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['userId', 'sku_id']); // Để tránh trùng sản phẩm yêu thích của cùng 1 user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
