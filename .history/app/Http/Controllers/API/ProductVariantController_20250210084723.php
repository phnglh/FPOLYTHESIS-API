
namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index()
    {
        return ProductVariant::all();
    }

    public function store(Request $request)
    {
        $productVariant = ProductVariant::create($request->all());
        return response()->json($productVariant, 201);
    }

    public function show($id)
    {
        return ProductVariant::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->update($request->all());
        return response()->json($productVariant, 200);
    }

    public function destroy($id)
    {
        ProductVariant::destroy($id);
        return response()->json(null, 204);
    }
}
