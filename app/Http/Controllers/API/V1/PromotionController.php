<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends BaseController
{
    private $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index()
    {
        $promotions = $this->promotionService->getAllPromotions();

        return $this->successResponse($promotions, 'Promotions retrieved successfully.');
    }

    public function store(Request $request)
    {

        $promotion = $this->promotionService->createPromotion($request->all());

        return $this->successResponse($promotion, 'Promotion created successfully.');
    }

    public function show($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);

        return $this->successResponse($promotion, 'Promotion details retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $promotion = $this->promotionService->getPromotionById($id);


        $updatedPromotion = $this->promotionService->updatePromotion($promotion, $request->all());

        return $this->successResponse($updatedPromotion, 'Promotion updated successfully.');
    }

    public function destroy($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);

        $this->promotionService->deletePromotion($promotion);

        return $this->successResponse(null, 'Promotion deleted successfully.');
    }
}
