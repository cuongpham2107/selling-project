<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Model properties
 *
 * @property int $id
 * @property int $buyer_id
 * @property int $seller_id
 * @property string|null $description
 * @property float $amount
 * @property int $duration
 * @property float $fee
 * @property string $status
 * @property int|null $chat_id
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property-read \App\Models\User $buyer
 * @property-read \App\Models\User $seller
 * @property-read \App\Models\Chat|null $chat
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id', 'seller_id', 'description', 'amount', 'duration', 'fee',
        'status', 'chat_id', 'confirmed_at', 'end_time', 'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function disputes(): MorphMany
    {
        return $this->morphMany(Dispute::class, 'transaction');
    }

    // Business Logic Helpers
    public static function calculateBaseFee($amount)
    {
        $tier = FeeTier::where('type', 'middle')
            ->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->where('max_amount', '>=', $amount)
                    ->orWhereNull('max_amount');
            })
            ->first();

        return $tier ? $tier->fee : 0;
    }

    public function calculateTotalFee()
    {
        $baseFee = self::calculateBaseFee($this->amount);

        // duration is stored in hours in the database
        $days = floor($this->duration / 24);

        if ($days >= 1) {
            // Add 20% of base fee for each day
            $additionalFee = $baseFee * 0.2 * $days;

            return $baseFee + $additionalFee;
        }

        return (float) $baseFee;
    }

    public static function calculatePoints($amount)
    {
        $tier = PointTier::where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->where('max_amount', '>=', $amount)
                    ->orWhereNull('max_amount');
            })
            ->first();

        return $tier ? $tier->points : 0;
    }
}
