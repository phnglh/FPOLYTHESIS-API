<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('current_page', 1);

        $products = $this->productService->getAllProducts($perPage, $currentPage);

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

    public function show($id)
    {
        $product = $this->productService->getProductById($id);

        return response()->json([
            'success' => true,
            'message' => 'Product details fetched successfully',
            'data' => $product,
            'errors' => null,
        ]);
    }

    public function store(Request $request)
    { 
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Kiểm tra ảnh
        ]);

        $imagePath = null;

        // Nếu có ảnh, tải lên và lưu đường dẫn
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
        ]);

        $product = $this->productService->createProduct($request->all());

        // dd($product);
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
            'errors' => null,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Kiểm tra ảnh
        ]);

        $product = Product::findOrFail($id); // Tìm sản phẩm theo ID

        $imagePath = $product->image_path; // Giữ lại đường dẫn ảnh cũ nếu không có ảnh mới

        // Nếu có ảnh mới, tải lên và cập nhật đường dẫn
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Tải ảnh mới lên
            $imagePath = $request->file('image')->store('products', 'public');
        }
        $product = $this->productService->updateProduct($id, $request->all());
          // Cập nhật sản phẩm
          $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
            'errors' => null,
        ]);
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'data' => null,
            'errors' => null,
        ], 200);
    }

}
