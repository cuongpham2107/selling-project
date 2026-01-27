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
 * @property int $points
 */
class PointTier extends Model
{
    use HasFactory;

    protected $fillable = ['min_amount', 'max_amount', 'points'];

    /**
     * Tính số điểm dựa trên số tiền giao dịch
     */
    public static function calculatePoints(float $amount): int
    {
        $tier = self::query()->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->where('max_amount', '>=', $amount)
                    ->orWhereNull('max_amount');
            })
            ->first();

        return $tier ? (int) $tier->points : 0;
    }
}
