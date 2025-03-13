<?php
namespace App\Services;

use App\Models\Promotion;
use App\Exceptions\ApiException; // them moi


class PromotionService
{
    public function getAllPromotions()
    {
        $AllPromotions = Promotion::all();

        if (!$AllPromotions) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        return $AllPromotions;
    }

    public function createPromotion($data)
    {
        return Promotion::create($data);
    }

    public function getPromotionById($id)
    {
        $PromotionById = Promotion::find($id);

        if (!$PromotionById) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        return $PromotionById;

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
