use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up()
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
}
