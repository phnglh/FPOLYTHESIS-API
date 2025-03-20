<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('payment_method', ['cod', 'vnpay', 'bank_transfer'])->index(); // Thêm 'bank_transfer' làm ví dụ
            $table->string('transaction_id')->nullable()->unique(); // Đảm bảo không trùng transaction_id
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index(); // Thêm 'refunded'
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable(); // Thêm để theo dõi thời gian hoàn tiền
            $table->text('payment_details')->nullable(); // Lưu thông tin chi tiết (VD: response từ VNPay)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
