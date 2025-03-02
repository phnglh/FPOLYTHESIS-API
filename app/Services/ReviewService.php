<?php

namespace App\Services;

use App\Models\Review;

class ReviewService
{
    public function getAllReviews()
    {
        return Review::all();
    }

    public function getReviewById($id)
    {
        return Review::findOrFail($id);
    }

    public function createReview($data)
    {
        return Review::create($data);
    }

    public function updateReview($id, $data)
    {
        $review = Review::findOrFail($id);
        $review->update($data);
        return $review;
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return true;
    }
}
