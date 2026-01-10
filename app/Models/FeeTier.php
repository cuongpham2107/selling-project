<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model properties
 *
 * @property int $id
 * @property float|null $min_amount
 * @property float|null $max_amount
 * @property float $fee
 */
class FeeTier extends Model
{
    use HasFactory;

    protected $fillable = ['min_amount', 'max_amount', 'fee'];

    /**
     * Tính phí cho giao dịch trung gian (theo cấu trúc bậc trong bảng fee_tiers)
     */
    public static function calculateFee(float $amount): float
    {
        $feeTier = self::where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->where('max_amount', '>=', $amount)
                    ->orWhereNull('max_amount');
            })
            ->first();

        return $feeTier ? $feeTier->fee : 0;
    }

    /**
     * Tính phí cho giao dịch gian hàng (theo phần trăm cấu hình trong config)
     */
    public static function calculateShopFee(float $amount): float
    {
        $feeConfig = config('transaction.shop_transaction_fee');

        if ($feeConfig['type'] === 'percentage') {
            return $amount * ($feeConfig['value'] / 100);
        }

        // Fixed fee
        return $feeConfig['value'];
    }

    /**
     * Tính thêm phí theo ngày cho giao dịch có thời gian >= 1 ngày
     */
    public static function calculateDailyFee(float $baseFee, int $durationHours): float
    {
        $threshold = config('transaction.daily_fee_threshold', 24);
        $multiplier = config('transaction.daily_fee_multiplier', 0.20);

        if ($durationHours < $threshold) {
            return 0;
        }

        $days = floor($durationHours / 24);

        return $baseFee * $multiplier * $days;
    }

    /**
     * Tính tổng phí cho giao dịch trung gian, bao gồm phí theo ngày
     */
    public static function calculateMiddleFee(float $amount, int $durationHours): float
    {
        $baseFee = self::calculateFee($amount);
        $dailyFee = self::calculateDailyFee($baseFee, $durationHours);

        return $baseFee + $dailyFee;
    }
}
