<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\VoucherRequest;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller

{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    // ✅ Danh sách voucher (Admin thấy tất cả, Customer chỉ thấy voucher còn hiệu lực)
public function index(Request $request)
{
    if (!$request->user()) {
        return response()->json(['message' => 'Token không hợp lệ hoặc user chưa đăng nhập!'], 401);
    }

    // Admin thấy tất cả, Customer chỉ thấy voucher còn hiệu lực
    $isAdmin = $request->user()->hasRole('admin');

    return response()->json($this->voucherService->list($isAdmin));
}


    // tạo voucher (Admin)
 public function store(VoucherRequest $request)
{
    Log::info("User từ request:", ['user' => $request->user()]);
    
    if (!$request->user()) {
        return response()->json(['message' => 'Token không hợp lệ hoặc user chưa đăng nhập!'], 401);
    }

    if (!$request->user()->hasRole('admin')) {
        return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
    }

    $voucher = $this->voucherService->create($request->validated());

    return response()->json([
        'message' => 'Voucher đã được tạo thành công!',
        'voucher' => $voucher
    ], 201);
}

    // cập nhật voucher (Admin)
    public function update(VoucherRequest $request, Voucher $voucher)
    {
        if (!$request->user()) {
        return response()->json(['message' => 'Token không hợp lệ hoặc user chưa đăng nhập!'], 401);
    }

    if (!$request->user()->hasRole('admin')) {
        return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
    }

        $updatedVoucher = $this->voucherService->update($voucher, $request->validated());

        return response()->json([
            'message' => 'Voucher đã được cập nhật thành công!',
            'voucher' => $updatedVoucher
        ]);
    }

    // xóa voucher (Admin)
    public function destroy(Request $request, Voucher $voucher)
    {
      if (!$request->user()) {
        return response()->json(['message' => 'Token không hợp lệ hoặc user chưa đăng nhập!'], 401);
    }

    if (!$request->user()->hasRole('admin')) {
        return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
    }

        $this->voucherService->delete($voucher);

        return response()->json(['message' => 'Voucher đã được xóa thành công!']);
    }

    // áp dụng voucher (Customer)
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0'
        ]);

        $result = $this->voucherService->apply($request->code, $request->order_total);

        return response()->json($result);
    }
}