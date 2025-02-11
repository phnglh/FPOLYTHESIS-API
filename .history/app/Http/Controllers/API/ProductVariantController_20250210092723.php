<?php
namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index()
    {
        return response()->json(ProductVariant::with('product')->get());
    }

    public function store(Request $request)
    {
        $variant = ProductVariant::create($request->all());
        return response()->json($variant, 201);
    }

    public function show($id)
    {
        return response()->json(ProductVariant::with('product')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->update($request->all());
        return response()->json($variant);
    }

    public function destroy($id)
    {
        ProductVariant::destroy($id);
        return response()->json(null, 204);
    }
}
