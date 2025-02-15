<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Bảng sản phẩm
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('categoryId')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('isPublished')->default(false);
            $table->timestamps();
        });

        // Bảng SKU (biến thể của sản phẩm)
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->foreignId('productId')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->timestamps();
        });

        // Bảng thuộc tính (Attribute)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Bảng giá trị thuộc tính (Attribute Values)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attributeId')->constrained('attributes')->cascadeOnDelete();
            $table->string('value'); // Ví dụ: XL, Red, Blue, M, S
            $table->timestamps();
        });

        // Bảng liên kết giữa SKU và giá trị thuộc tính
        Schema::create('attribute_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skuId')->constrained('skus')->cascadeOnDelete();
            $table->foreignId('attributeId')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attributeValueId')->constrained('attribute_values')->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('attribute_skus');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('skus');
        Schema::dropIfExists('products');
    }
};
