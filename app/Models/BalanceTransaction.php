<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * BalanceTransaction Model
 * 
 * Tracks all balance changes for users with polymorphic relationship to source transactions.
 * Used to display complete transaction history.
 */
class BalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'held_balance_after',
        'source_id',
        'source_type',
        'related_user_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'held_balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $appends = [
        'type_label',
        'amount_formatted',
    ];

    /**
     * Get the user who owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source of this transaction (polymorphic).
     * Can be: Deposit, Withdrawal, ShopTransaction, Transaction, Dispute, PointTransaction
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the related user (buyer/seller in transactions).
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get Vietnamese label for transaction type.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'Nạp tiền',
            'withdrawal' => 'Rút tiền',
            'purchase' => 'Mua hàng',
            'sale' => 'Bán hàng',
            'hold' => 'Giữ tiền',
            'release' => 'Giải phóng tiền',
            'refund' => 'Hoàn tiền',
            'point_redeem' => 'Đổi điểm',
            'fee' => 'Phí giao dịch',
            'dispute_refund' => 'Hoàn tiền tranh chấp',
            'dispute_payout' => 'Thanh toán tranh chấp',
            'middleman_purchase' => 'Mua qua trung gian',
            'middleman_sale' => 'Bán qua trung gian',
            default => 'Khác',
        };
    }

    /**
     * Get formatted amount with sign.
     */
    public function getAmountFormattedAttribute(): string
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format((float) $this->amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Check if this is a money-in transaction.
     */
    public function isIncome(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if this is a money-out transaction.
     */
    public function isExpense(): bool
    {
        return $this->amount < 0;
    }
}
