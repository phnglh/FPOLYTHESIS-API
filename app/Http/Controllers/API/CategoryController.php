<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            "success" => true,
            "message" => "Request processed successfully",
            "data" => $categories,
            "errors" => null
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique',
            'description' => 'nullable|string',
        ]);
        if ($validator->fail()) {
            return response()->json([
                "success" => false,
                "message" => "Sai Định Dạng",
                "data" => null,
                "errors" => $validator->errors()
            ], 422);
        }

        $categories = Category::create($validator);
        return response()->json([
            "success" => true,
            "message" => "Tạo Danh Mục Thành Công",
            "data" => $categories,
            "errors" => null
        ]);
    }
    public function update(Request $request, $id)
    {
        $categories = Category::find($id);
        if(!$categories){
            return response()->json([
                "success" => false,
                "message" => "Sai Định Dạng",
                "data" => null,
                "errors" => null
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'description' => 'nullable|string',
        ]);
        if ($validator->fail()) {
            return response()->json([
                "success" => false,
                "message" => "Sai Định Dạng",
                "data" => null,
                "errors" => $validator->errors()
            ], 422);
        }

        $categories->upadate($request->all());
        return response()->json([
            "success" => true,
            "message" => "Sửa Danh Mục Thành Công",
            "data" => $categories,
            "errors" => null
        ]);
    }
    public function show($id)
    {
        $categories = Category::find($id);
        if (!$categories) {
            return response()->json([
                "success" => false,
                "message" => "Sai Định Dạng",
                "data" => null,
                "errors" => null
            ], 404);
        }
        return response()->json([
            "success" => true,
            "message" => "",
            "data" => $categories,
            "errors" => null
        ], 201);
    }
    public function destroy($id)
    {
        $categories = Category::find($id);
        if (!$categories) {
            return response()->json([
                "success" => false,
                "message" => "Xoá Danh Mục Thất Bại",
                "data" => null,
                "errors" => null
            ], 404);
        }
        $categories->delete();
        return response()->json([
            "success" => true,
            "message" => "Xoá Danh Mục Thành Công",
            "data" => null,
            "errors" => null
        ], 201);
    }
}
