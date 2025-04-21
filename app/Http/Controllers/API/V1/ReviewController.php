<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use App\Services\ReviewService;

class ReviewController extends BaseController
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    // Lấy danh sách review theo product
    public function index($productId)
    {
        $reviews = $this->reviewService->getReviewsByProduct($productId);
        return $this->successResponse($reviews, 'FETCH_REVIEWS_SUCCESS');
    }

    // Tạo mới review
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $review = $this->reviewService->createReview($validated);

        return response()->json($review, 201);
    }

    // Xoá review
    public function destroy($id)
    {
        $this->reviewService->deleteReview($id);

        return response()->json(['message' => 'Xoá đánh giá thành công']);
    }
}
