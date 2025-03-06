<?php
namespace App\Services;

use App\Models\Promotion;

class PromotionService
{
    public function getAllPromotions()
    {
        return Promotion::all();
    }

    public function createPromotion($data)
    {
        return Promotion::create($data);
    }

    public function getPromotionById($id)
    {
        return Promotion::find($id);
    }

    public function updatePromotion($promotion, $data)
    {
        $promotion->update($data);
        return $promotion;
    }

    public function deletePromotion($promotion)
    {
        $promotion->delete();
        return true;
    }
}
