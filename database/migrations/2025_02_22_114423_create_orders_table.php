<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        // Tạo bảng shipping_methods
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->string('estimated_time');
            $table->boolean('is_express')->default(0);
            $table->timestamps();
        });

        // Tạo bảng orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->onDelete('set null');
            $table->foreignId('address_id')->constrained('user_addresses')->cascadeOnDelete();

            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])
                ->default('pending')->index();
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'refunded'])
                ->default('unpaid')->index();


            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('final_total', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        // Tạo bảng order_items
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

        // Tạo bảng order_status_histories
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

        // Tạo bảng order_logs
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

    public function down()
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
    }
};
