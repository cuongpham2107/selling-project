<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Balance Transaction Service
 *
 * Centralized service for recording all balance changes with automatic transaction logging.
 */
class BalanceTransactionService
{
    /**
     * Record a balance transaction.
     *
     * @param  User  $user  The user whose balance is changing
     * @param  string  $type  Transaction type (deposit, withdrawal, purchase, sale, etc.)
     * @param  float  $amount  Amount (positive for income, negative for expense)
     * @param  Model|null  $source  Source model (Deposit, ShopTransaction, etc.)
     * @param  int|null  $relatedUserId  Related user (buyer/seller in transactions)
     * @param  string|null  $description  Optional description
     * @param  array|null  $metadata  Optional metadata
     */
    public static function record(
        User $user,
        string $type,
        float $amount,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null,
        string $currency = 'vnd'
    ): BalanceTransaction {
        $balance_after = $user->balance->balance;
        $held_balance_after = $user->balance->held_balance;
        $points_after = $user->point->points ?? 0;

        return BalanceTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'balance_after' => $balance_after,
            'held_balance_after' => $held_balance_after,
            'points_after' => $points_after,
            'source_id' => $source?->id,
            'source_type' => $source ? get_class($source) : null,
            'related_user_id' => $relatedUserId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Increment user balance and record transaction.
     */
    public static function incrementBalance(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->increment('balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, $amount, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Decrement user balance and record transaction.
     */
    public static function decrementBalance(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->decrement('balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, -$amount, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Increment held balance and record transaction.
     */
    public static function incrementHeldBalance(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->increment('held_balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, 0, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Decrement held balance and record transaction.
     */
    public static function decrementHeldBalance(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->decrement('held_balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, 0, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Hold funds (move from balance to held_balance).
     */
    public static function hold(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->decrement('balance', $amount);
        $user->balance->increment('held_balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, -$amount, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Release held funds (move from held_balance to balance).
     */
    public static function release(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->balance->decrement('held_balance', $amount);
        $user->balance->increment('balance', $amount);
        $user->balance->refresh();

        return self::record($user, $type, $amount, $source, $relatedUserId, $description, $metadata);
    }

    /**
     * Increment user points and record transaction.
     */
    public static function incrementPoints(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->point->increment('points', $amount);
        $user->point->refresh();

        return self::record(
            user: $user,
            type: $type,
            amount: $amount,
            source: $source,
            relatedUserId: $relatedUserId,
            description: $description,
            metadata: $metadata,
            currency: 'point'
        );
    }

    /**
     * Decrement user points and record transaction.
     */
    public static function decrementPoints(
        User $user,
        float $amount,
        string $type,
        ?Model $source = null,
        ?int $relatedUserId = null,
        ?string $description = null,
        ?array $metadata = null
    ): BalanceTransaction {
        $user->point->decrement('points', $amount);
        $user->point->refresh();

        return self::record(
            user: $user,
            type: $type,
            amount: -$amount,
            source: $source,
            relatedUserId: $relatedUserId,
            description: $description,
            metadata: $metadata,
            currency: 'point'
        );
    }
}
