<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Review; // them moi

class ReviewService
{
    public function getAllReviews()
    {
        return Review::all();
    }

    public function getReviewById($id)
    {
        $ReviewById = Review::findOrFail($id);

        if (! $ReviewById) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        } // ko tồn tại

        return $ReviewById;
    }

    public function createReview($data)
    {
        return Review::create($data);
    }

    public function updateReview($id, $data)
    {
        $review = Review::findOrFail($id);

        if (! $review) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        } // ko tồn tại

        $review->update($data);

        return $review;
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        if (! $review) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        $review->delete();

        return true;
    }
}
