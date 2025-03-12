<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    //
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        $reviews = $this->reviewService->getAllReviews();
        return $this->successResponse($reviews, "Reviews retrieved successfully.");
    }

    public function show($id)
    {
        $review = $this->reviewService->getReviewById($id);
        if (!$review) {
            return $this->errorResponse("REVIEW_NOT_FOUND", "Review not found.", 404);
        }
        return $this->successResponse($review, "Review details retrieved successfully.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $review = $this->reviewService->createReview($validated);
        return $this->successResponse($review, "Review created successfully.");
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $updatedReview = $this->reviewService->updateReview($id, $validated);
        if (!$updatedReview) {
            return $this->errorResponse("REVIEW_NOT_FOUND", "Review not found.", 404);
        }

        return $this->successResponse($updatedReview, "Review updated successfully.");
    }

    public function destroy($id)
    {
        $deleted = $this->reviewService->deleteReview($id);
        if (!$deleted) {
            return $this->errorResponse("REVIEW_NOT_FOUND", "Review not found.", 404);
        }

        return $this->successResponse(null, "Review deleted successfully.");
    }
}