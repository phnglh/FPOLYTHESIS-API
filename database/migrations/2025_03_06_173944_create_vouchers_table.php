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
        Schema::create('vouchers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Mã giảm giá
        $table->enum('type', ['percentage', 'fixed']); // Loại giảm giá (theo % hoặc số tiền cố định)
        $table->decimal('discount_value', 10, 2); // Giá trị giảm giá
        $table->decimal('min_order_value', 10, 2)->nullable(); // Giá trị đơn hàng tối thiểu để áp dụng
        $table->integer('usage_limit')->nullable(); // Giới hạn số lần sử dụng
        $table->integer('used_count')->default(0); // Số lần đã sử dụng
        $table->dateTime('start_date')->nullable();
        $table->dateTime('end_date')->nullable();
        $table->boolean('is_active')->default(true); // Trạng thái mã giảm giá
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};