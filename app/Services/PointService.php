<?php

namespace App\Services;

use App\Filament\Resources\ShopTransactions\Enums\Status as ShopStatus;
use App\Models\PointTier;
use App\Models\Setting;
use App\Models\ShopTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PointService
{
    /**
     * Distribute points to buyer and referrer based on transaction amount.
     */
    public static function distributePointsForTransaction(User $buyer, float $amount, Model $source, ?int $sellerId = null): void
    {
        $points = PointTier::calculatePoints($amount);
        if ($points <= 0) {
            return;
        }

        $limit = Setting::getValue('point_total_limit', 50000000);
        $distributed = Setting::getValue('point_total_distributed', 0);

        if ($distributed >= $limit) {
            return;
        }

        $pointsToAward = min($points, $limit - $distributed);

        // 1. Award Points to buyer and update distributed total
        Setting::setValue('point_total_distributed', $distributed + $pointsToAward);

        // Record point earning
        BalanceTransactionService::incrementPoints(
            user: $buyer,
            amount: $pointsToAward,
            type: 'point_earn',
            source: $source,
            relatedUserId: $sellerId,
            description: "Nhận {$pointsToAward} điểm từ giao dịch #{$source->id}",
            metadata: [
                'points_earned' => $pointsToAward,
                'amount' => $amount,
            ]
        );

        // 2. Referral logic
        $referrer = $buyer->referredBy;
        if ($referrer) {
            $previousCount = Transaction::query()->where('buyer_id', $buyer->id)
                ->where('status', 'completed')
                ->where(function ($query) use ($source) {
                    if ($source instanceof Transaction) {
                        $query->where('id', '!=', $source->id);
                    }
                })
                ->count() +
                ShopTransaction::query()->where('buyer_id', $buyer->id)
                    ->where('status', ShopStatus::Completed)
                    ->where(function ($query) use ($source) {
                        if ($source instanceof ShopTransaction) {
                            $query->where('id', '!=', $source->id);
                        }
                    })
                    ->count();

            if ($previousCount === 0) {
                // First time: 100% match
                self::awardReferralPoints($referrer, $buyer, $pointsToAward, $source, 'first_transaction', '100%');
            } else {
                // Recurring: 10% match
                $recurringPoints = floor($pointsToAward * 0.1);
                if ($recurringPoints > 0) {
                    self::awardReferralPoints($referrer, $buyer, $recurringPoints, $source, 'recurring', '10%');
                }
            }
        }
    }

    /**
     * Award points to referrer with pool check.
     */
    protected static function awardReferralPoints(User $referrer, User $buyer, float $points, Model $source, string $type, string $label): void
    {
        $limit = Setting::getValue('point_total_limit', 50000000);
        $currentDistributed = Setting::getValue('point_total_distributed', 0);

        if ($currentDistributed < $limit) {
            $referralPoints = min($points, $limit - $currentDistributed);

            // Update distributed total
            Setting::setValue('point_total_distributed', $currentDistributed + $referralPoints);

            BalanceTransactionService::record(
                user: $referrer,
                type: 'point_earn',
                amount: $referralPoints,
                source: $source,
                relatedUserId: $buyer->id,
                description: "Thưởng giới thiệu: {$referralPoints} điểm ({$label})",
                metadata: [
                    'points_earned' => $referralPoints,
                    'referral_type' => $type,
                    'referred_user' => $buyer->username,
                ],
                currency: 'point'
            );
        }
    }
}
