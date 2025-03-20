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
    public function apply($voucherCode, $orderSubtotal)
    {
        $voucher = Voucher::where('code', $voucherCode)
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$voucher) {
            return ['success' => false, 'message' => 'Mã giảm giá không hợp lệ!'];
        }

        if ($orderSubtotal < $voucher->min_order_value) {
            return ['success' => false, 'message' => 'Đơn hàng không đủ điều kiện để áp dụng mã giảm giá!'];
        }

        $discountAmount = 0;
        if ($voucher->type === 'percentage') {
            $discountAmount = ($voucher->discount_value / 100) * $orderSubtotal;
        } elseif ($voucher->type === 'fixed') {
            $discountAmount = $voucher->discount_value;
        }

        return ['success' => true, 'discount' => $discountAmount];
    }
}
