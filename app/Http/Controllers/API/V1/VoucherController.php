<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\VoucherRequest;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends BaseController
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    // danh sách voucher (Admin thấy tất cả, Customer chỉ thấy voucher còn hiệu lực)
    public function index(Request $request)
    {
        if (! $request->user()) {
            return $this->errorResponse('UNAUTHORIZED', 'Invalid token or user not logged in.', 401);
        }

        $isAdmin = $request->user()->hasRole('admin');
        $vouchers = $this->voucherService->list($isAdmin);

        return $this->successResponse($vouchers, 'Vouchers retrieved successfully.');
    }

    // tạo voucher (Admin)
    public function store(VoucherRequest $request)
    {
        Log::info('User from request:', ['user' => $request->user()]);

        if (! $request->user()) {
            return $this->errorResponse('UNAUTHORIZED', 'Invalid token or user not logged in.', 401);
        }

        if (! $request->user()->hasRole('admin')) {
            return $this->errorResponse('FORBIDDEN', 'You do not have permission to perform this action.', 403);
        }

        $voucher = $this->voucherService->create($request->validated());

        return $this->successResponse($voucher, 'Voucher created successfully.');
    }

    // cập nhật voucher (Admin)
    public function update(VoucherRequest $request, Voucher $voucher)
    {
        if (! $request->user()) {
            return $this->errorResponse('UNAUTHORIZED', 'Invalid token or user not logged in.', 401);
        }

        if (! $request->user()->hasRole('admin')) {
            return $this->errorResponse('FORBIDDEN', 'You do not have permission to perform this action.', 403);
        }

        $updatedVoucher = $this->voucherService->update($voucher, $request->validated());

        return $this->successResponse($updatedVoucher, 'Voucher updated successfully.');
    }

    // xóa voucher (Admin)
    public function destroy(Request $request, Voucher $voucher)
    {
        if (! $request->user()) {
            return $this->errorResponse('UNAUTHORIZED', 'Invalid token or user not logged in.', 401);
        }

        if (! $request->user()->hasRole('admin')) {
            return $this->errorResponse('FORBIDDEN', 'You do not have permission to perform this action.', 403);
        }

        $this->voucherService->delete($voucher);

        return $this->successResponse(null, 'Voucher deleted successfully.');
    }

    // áp dụng voucher (Customer)
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $result = $this->voucherService->apply($request->code, $request->order_total);

        if (! $result) {
            return $this->errorResponse('INVALID_VOUCHER', 'The voucher code is invalid or expired.', 400);
        }

        return $this->successResponse($result, 'Voucher applied successfully.');
    }
}
