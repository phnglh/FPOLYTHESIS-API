public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->foreignId('brand_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}