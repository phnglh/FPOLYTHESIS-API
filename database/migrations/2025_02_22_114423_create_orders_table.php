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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('finalTotal', 10, 2)->default(0);
            $table->string('shippingAddress');
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orderId')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('skuId')->constrained('skus')->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('totalPrice', 10, 2);
            $table->text('productAttributes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orderId')->constrained('orders')->cascadeOnDelete();
            $table->enum('oldStatus', ['pending', 'processing', 'shipped', 'delivered', 'cancelled']);
            $table->enum('newStatus', ['pending', 'processing', 'shipped', 'delivered', 'cancelled']);
            $table->foreignId('changedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('changed_at')->useCurrent();
        });

        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orderId')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('userId')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamp('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
    }
};
