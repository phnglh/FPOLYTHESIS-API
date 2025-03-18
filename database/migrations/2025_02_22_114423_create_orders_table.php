<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            $table->string('order_number')->unique();

            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])
                ->default('pending');

            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'failed'])
                ->default('unpaid');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('final_total', 10, 2)->default(0);

            $table->string('shipping_address');
            $table->string('shipping_method')->nullable();
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered', 'failed'])->default('pending');

            $table->string('coupon_code')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });


        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');

            $table->string('product_name');
            $table->string('sku_code');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity')->unsigned();
            $table->decimal('total_price', 10, 2);

            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('old_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']);
            $table->enum('new_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
