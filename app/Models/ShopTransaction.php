<?php

namespace App\Models;

use App\Filament\Resources\ShopTransactions\Enums\Status;
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
 * @property int $product_id
 * @property float $amount
 * @property float $fee
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $chat_id
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property array|null $product_data
 */
class ShopTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id', 'seller_id', 'product_id', 'amount', 'fee',
        'status', 'chat_id', 'end_time', 'completed_at', 'product_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'status' => Status::class,
        'product_data' => 'array',
    ];

    protected $appends = [
        'transaction_type',
    ];

    public function getTransactionTypeAttribute(): string
    {
        if (! auth()->check()) {
            return 'Unknown';
        }

        return $this->buyer_id === auth()->id() ? 'Đơn mua' : 'Đơn bán';
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function disputes(): MorphMany
    {
        return $this->morphMany(Dispute::class, 'transaction');
    }
}
