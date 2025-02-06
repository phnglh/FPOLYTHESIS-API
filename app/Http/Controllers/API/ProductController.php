<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10); 
        $currentPage = $request->query('current_page', 1); 

        $products = Product::with('category')
            ->paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json([
            'success' => true,
            'message' => 'Product list fetched successfully',
            'data' => [
                'products' => $products->items(),
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ]
            ],
            'errors' => null,
        ]);
    }
}
