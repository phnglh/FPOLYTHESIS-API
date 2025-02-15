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
        Schema::create('attribute_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skuId')->constrained('skus')->cascadeOnDelete();
            $table->foreignId('attributeValueId')->constrained('attribute_values')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_skus');
    }
};
