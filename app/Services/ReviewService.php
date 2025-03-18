<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function getAllReviews(): Collection
    {
        return Review::all();
    }

    public function getReviewById(int $id): Review
    {
        try {
            return Review::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Không tìm thấy review', 404);
        }
    }


    public function createReview(array $data): Review
    {
        try {
            return DB::transaction(fn() => Review::create($data));
        } catch (\Exception $e) {
            throw new ApiException('Lỗi khi tạo review: ' . $e->getMessage(), 500);
        }
    }


    public function updateReview(int $id, array $data): Review
    {
        try {
            $review = Review::findOrFail($id);
            DB::transaction(fn() => $review->update($data));
            return $review;
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Không tìm thấy review để cập nhật', 404);
        } catch (\Exception $e) {
            throw new ApiException('Lỗi khi cập nhật review: ' . $e->getMessage(), 500);
        }
    }

    public function deleteReview(int $id): bool
    {
        try {
            $review = Review::findOrFail($id);
            DB::transaction(fn() => $review->delete());
            return true;
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Không tìm thấy review để xóa', 404);
        } catch (\Exception $e) {
            throw new ApiException('Lỗi khi xóa review: ' . $e->getMessage(), 500);
        }
    }
}
