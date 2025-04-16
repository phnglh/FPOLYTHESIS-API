<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ReportFlames\RevenueReportResource;
use App\Http\Resources\ReportFlames\OrderReportResource;
use App\Http\Resources\ReportFlames\ProductReportResource;
use App\Http\Resources\ReportFlames\CustomerReportResource;
use App\Http\Resources\ReportFlames\InventoryReportResource;
use App\Services\ReportService;

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
            return $this->successResponse(
                RevenueReportResource::collection($data),
                'GET_REVENUE_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('REVENUE_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getOrderReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getOrderReport($filters);
            return $this->successResponse(
                OrderReportResource::collection($data),
                'GET_ORDER_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('ORDER_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getProductReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getProductReport($filters);
            return $this->successResponse(
                ProductReportResource::collection($data),
                'GET_PRODUCT_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('PRODUCT_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getCustomerReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getCustomerReport($filters);
            return $this->successResponse(
                CustomerReportResource::collection($data),
                'GET_CUSTOMER_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('CUSTOMER_REPORT_ERROR', $e->getMessage(), 500);
        }
    }

    public function getInventoryReport(ReportRequest $request)
    {
        try {
            $filters = $request->validated();
            $data = $this->reportService->getInventoryReport($filters);
            return $this->successResponse(
                InventoryReportResource::collection($data),
                'GET_INVENTORY_REPORT_SUCCESS'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('INVENTORY_REPORT_ERROR', $e->getMessage(), 500);
        }
    }
}
