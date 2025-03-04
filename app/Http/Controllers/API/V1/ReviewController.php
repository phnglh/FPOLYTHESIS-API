<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    //
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        return response()->json($this->reviewService->getAllReviews(), 200);
    }

    public function show($id)
    {
        return response()->json($this->reviewService->getReviewById($id), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        return response()->json($this->reviewService->createReview($validated), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        return response()->json($this->reviewService->updateReview($id, $validated), 200);
    }

    public function destroy($id)
    {
        $this->reviewService->deleteReview($id);
        return response()->json(['message' => 'Review deleted'], 200);
    }
}
