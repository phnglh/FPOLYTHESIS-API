public function up()
{
    Schema::create('product_variants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        $table->string('sku')->unique()->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->timestamps();
    });
}