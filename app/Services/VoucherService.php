<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    // cập nhật voucher (Admin)
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
        return Voucher::when(! $isAdmin, function ($query) {
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
    public function apply($code, $order_total)
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (! $voucher) {
            return ['success' => false, 'message' => 'Mã giảm giá không tồn tại!'];
        }

        if (! $voucher->isValid()) {
            return ['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn!'];
        }

        if ($voucher->min_order_value && $order_total < $voucher->min_order_value) {
            return ['success' => false, 'message' => 'Đơn hàng không đủ điều kiện áp dụng mã giảm giá!'];
        }

        // Kiểm tra giới hạn số lần sử dụng
        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            return ['success' => false, 'message' => 'Mã giảm giá đã đạt giới hạn sử dụng!'];
        }

        $discount = $voucher->type === 'percentage'
            ? ($order_total * $voucher->discount_value / 100)
            : $voucher->discount_value;

        // Cập nhật số lần sử dụng
        $voucher->increment('used_count');

        return [
            'success' => true,
            'discount' => min($discount, $order_total), // Giảm giá không vượt quá tổng đơn hàng
            'message' => 'Mã giảm giá được áp dụng thành công!',
        ];
    }
}
