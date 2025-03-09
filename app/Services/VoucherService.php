<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class VoucherService
{
    // cạo voucher (Admin)
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Voucher::create([
                'code' => strtoupper($data['code']), // Chuyển code thành chữ in hoa
                'type' => $data['type'],
                'discount_value' => $data['discount_value'],
                'min_order_value' => $data['min_order_value'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'used_count' => 0,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    //cập nhật voucher (Admin)
    public function update(Voucher $voucher, array $data)
    {
        return DB::transaction(function () use ($voucher, $data) {
            $voucher->update([
                'code' => strtoupper($data['code']),
                'type' => $data['type'],
                'discount_value' => $data['discount_value'],
                'min_order_value' => $data['min_order_value'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $voucher;
        });
    }

    // xóa voucher (Admin)
    public function delete(Voucher $voucher)
    {
        return DB::transaction(function () use ($voucher) {
            return $voucher->delete();
        });
    }

    // danh sách voucher (Admin & Customer)
    public function list($isAdmin = false)
    {
        return Voucher::when(!$isAdmin, function ($query) {
            $query->where('is_active', true)
                  ->where(function ($q) {
                      $q->whereNull('start_date')->orWhere('start_date', '<=', Carbon::now());
                  })
                  ->where(function ($q) {
                      $q->whereNull('end_date')->orWhere('end_date', '>=', Carbon::now());
                  });
        })->get();
    }

    // kiểm tra & áp dụng voucher (Customer)
    public function apply($code, $orderTotal)
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (!$voucher) {
            return ['success' => false, 'message' => 'Mã giảm giá không tồn tại!'];
        }

        if (!$voucher->isValid()) {
            return ['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn!'];
        }

        if ($voucher->min_order_value && $orderTotal < $voucher->min_order_value) {
            return ['success' => false, 'message' => 'Đơn hàng không đủ điều kiện áp dụng mã giảm giá!'];
        }

        $discount = $voucher->type === 'percentage'
            ? ($orderTotal * $voucher->discount_value / 100)
            : $voucher->discount_value;

        return [
            'success' => true,
            'discount' => min($discount, $orderTotal), // Giảm giá không được vượt quá tổng đơn hàng
            'message' => 'Mã giảm giá được áp dụng thành công!'
        ];
    }
}