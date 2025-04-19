<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\VoucherRequest;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use App\Http\Resources\Voucher\VoucherResource;
use App\Models\Voucher; // Thêm dòng này

class VoucherController extends BaseController
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function index(Request $request)
    {
        $isAdmin = $request->user()->hasRole('admin');
        $vouchers = $this->voucherService->list($isAdmin);

        return $this->successResponse(VoucherResource::Collection($vouchers), 'Vouchers retrieved successfully.');
    }

    public function store(VoucherRequest $request)
    {
        $voucher = $this->voucherService->create($request->validated());

        return $this->successResponse(new VoucherResource($voucher), 'Voucher created successfully.');
    }

    public function update(VoucherRequest $request, $voucher)
    {
        if (!$request->user()) {
            return $this->errorResponse('UNAUTHORIZED', 'Invalid token or user not logged in.', 401);
        }

        if (!$request->user()->hasRole('admin')) {
            return $this->errorResponse('FORBIDDEN', 'You do not have permission to perform this action.', 403);
        }

        // Tìm voucher theo ID
        $voucherModel = Voucher::find($voucher);
        if (!$voucherModel) {
            return $this->errorResponse('NOT_FOUND', 'Voucher not found.', 404);
        }

        $updatedVoucher = $this->voucherService->update($voucherModel, $request->validated());

        return $this->successResponse(new VoucherResource($updatedVoucher), 'Voucher updated successfully.');
    }

    public function destroy(Request $request, Voucher $voucher)
    {
        $this->voucherService->delete($voucher);

        return $this->successResponse(null, 'Voucher deleted successfully.');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $result = $this->voucherService->apply($request->code, $request->order_total);

        return $this->successResponse($result, 'Voucher applied successfully.');
    }
}
