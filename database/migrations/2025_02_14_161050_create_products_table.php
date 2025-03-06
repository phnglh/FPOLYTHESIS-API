<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Bảng sản phẩm
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brandId')->nullable()->constrained('brands')->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Bảng SKU (biến thể của sản phẩm)
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->timestamps();
        });

        // Bảng thuộc tính (Attribute)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Bảng giá trị thuộc tính (Attribute Values)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value'); // Ví dụ: XL, Red, Blue, M, S
            $table->timestamps();
        });

        Schema::create('attribute_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->string('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_skus', function (Blueprint $table) {
            $table->dropForeign(['skuId']);
            $table->dropForeign(['attribute_value_id']);
        });
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('skus');
        Schema::dropIfExists('products');
    }
};