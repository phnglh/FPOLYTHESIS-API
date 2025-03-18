<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\ReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }


    public function index(): JsonResponse
    {
        $reviews = $this->reviewService->getAllReviews();
        return response()->json(['status' => 'success', 'data' => $reviews], 200);
    }


    public function show(int $id): JsonResponse
    {
        $review = $this->reviewService->getReviewById($id);
        return response()->json(['status' => 'success', 'data' => $review], 200);
    }


    public function store(ReviewRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['user_id' => auth()->id()]);
        $review = $this->reviewService->createReview($data);
        return response()->json(['status' => 'success', 'data' => $review], 201);
    }


    public function update(ReviewRequest $request, int $id): JsonResponse
    {
        $data = array_merge($request->validated(), ['user_id' => auth()->id()]);
        $review = $this->reviewService->updateReview($id, $data);
        return response()->json(['status' => 'success', 'data' => $review], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->reviewService->deleteReview($id);
        return response()->json(['status' => 'success', 'message' => 'Review đã được xóa'], 200);
    }
}
