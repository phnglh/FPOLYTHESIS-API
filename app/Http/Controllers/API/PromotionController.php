<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    private $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    // Lấy danh sách tất cả khuyến mãi
    public function index()
    {
        $promotions = $this->promotionService->getAllPromotions();
        return response()->json($promotions);
    }

    // Tạo khuyến mãi mới
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $promotion = $this->promotionService->createPromotion($request->all());
        return response()->json($promotion, 201);
    }

    // Lấy thông tin khuyến mãi theo id
    public function show($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }
        return response()->json($promotion);
    }

    // Cập nhật thông tin khuyến mãi
    public function update(Request $request, $id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'discount_percentage' => 'numeric|min:0|max:100',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $promotion = $this->promotionService->updatePromotion($promotion, $request->all());
        return response()->json($promotion);
    }

    // Xóa khuyến mãi
    public function destroy($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $this->promotionService->deletePromotion($promotion);
        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}
