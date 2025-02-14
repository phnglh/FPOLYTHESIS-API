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
        Schema::create('AttributeSkus', function (Blueprint $table) {
            $table->primary(['attributeId', 'skuId']);
            $table->foreignId('attributeId')->constrained('Attributes')->cascadeOnDelete();
            $table->foreignId('skuId')->constrained('Skus')->cascadeOnDelete();
            $table->string('value')->comment('The value for this SKU and attribute combination, i.e. Small, Red, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AttributeSkus');
    }
};
