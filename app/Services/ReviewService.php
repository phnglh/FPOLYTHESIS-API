<?php

namespace App\Services;

use App\Models\Review;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReviewService
{
    public function getReviewsByProduct($productId)
    {
        try {
            return Review::with('user')
                ->where('product_id', $productId)
                ->latest()
                ->get();
        } catch (\Throwable $e) {
            throw new ApiException("Không thể lấy danh sách đánh giá.", 500);
        }
    }

    public function createReview(array $data)
    {
        try {
            return Review::create([
                'user_id' => Auth::id(),
                'product_id' => $data['product_id'],
                'rating' => $data['rating'],
                'review' => $data['review'],
            ]);
        } catch (\Throwable $e) {
            throw new ApiException("Không thể tạo đánh giá.", 500);
        }
    }

    public function deleteReview($reviewId)
    {
        try {
            $review = Review::findOrFail($reviewId);

            if ($review->user_id !== Auth::id()) {
                throw new ApiException("Bạn không có quyền xoá đánh giá này.", 403);
            }

            $review->delete();
        } catch (ModelNotFoundException $e) {
            throw new ApiException("Không tìm thấy đánh giá.", 404);
        } catch (\Throwable $e) {
            throw new ApiException("Lỗi khi xoá đánh giá.", 500);
        }
    }
}
