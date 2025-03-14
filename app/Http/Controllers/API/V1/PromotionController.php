<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends BaseController
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

        return $this->successResponse($promotions, 'Promotions retrieved successfully.');
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
            return $this->errorResponse('VALIDATION_ERROR', 'Validation failed.', 400, $validator->errors());
        }

        $promotion = $this->promotionService->createPromotion($request->all());

        return $this->successResponse($promotion, 'Promotion created successfully.');
    }

    // Lấy thông tin khuyến mãi theo id
    public function show($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (! $promotion) {
            return $this->errorResponse('PROMOTION_NOT_FOUND', 'Promotion not found.', 404);
        }

        return $this->successResponse($promotion, 'Promotion details retrieved successfully.');
    }

    // Cập nhật thông tin khuyến mãi
    public function update(Request $request, $id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (! $promotion) {
            return $this->errorResponse('PROMOTION_NOT_FOUND', 'Promotion not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'discount_percentage' => 'numeric|min:0|max:100',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', 'Validation failed.', 400, $validator->errors());
        }

        $updatedPromotion = $this->promotionService->updatePromotion($promotion, $request->all());

        return $this->successResponse($updatedPromotion, 'Promotion updated successfully.');
    }

    // Xóa khuyến mãi
    public function destroy($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        if (! $promotion) {
            return $this->errorResponse('PROMOTION_NOT_FOUND', 'Promotion not found.', 404);
        }

        $this->promotionService->deletePromotion($promotion);

        return $this->successResponse(null, 'Promotion deleted successfully.');
    }
}
