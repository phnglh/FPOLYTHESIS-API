<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\ReportRequest;
use App\Services\ReportService;
use App\Http\Resources\ReportFlames\{
    ProductReportResource,
    CustomerReportResource,
    RevenueByCategoryResource,
    RevenueStatisticsResource
};
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getRevenueReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getRevenueReport($filters);
            return $this->successResponse($data, 'GET_REVENUE_REPORT_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('REVENUE_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getOrderReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getOrderReport($filters);
            return $this->successResponse($data, 'GET_ORDER_REPORT_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('ORDER_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getCancelRate(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getCancelRate($filters);
            return $this->successResponse($data, 'GET_CANCEL_RATE_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('CANCEL_RATE_ERROR', $e->getMessage(), 500);
        }
    }

    public function getRevenueByCategory(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getRevenueByCategory($filters);
            return $this->successResponse(
                RevenueByCategoryResource::collection($data),
                'GET_REVENUE_BY_CATEGORY_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('REVENUE_BY_CATEGORY_ERROR', $e->getMessage(), 500);
        }
    }

    public function getTopProductReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getTopProductReport($filters);
            return $this->successResponse(
                ProductReportResource::collection($data),
                'GET_TOP_PRODUCT_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('TOP_PRODUCT_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getTopCustomerReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getTopCustomerReport($filters);
            return $this->successResponse(
                CustomerReportResource::collection($data),
                'GET_TOP_CUSTOMER_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('TOP_CUSTOMER_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $statistics = $this->reportService->getRevenueStatistics($request);
            return response()->json([
                'success' => true,
                'data' => new RevenueStatisticsResource($statistics),
                'message' => 'Lấy thống kê doanh thu thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'ERROR',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
