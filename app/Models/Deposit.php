<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model properties
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $method
 * @property string $status
 * @property-read \App\Models\User $user
 */
class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'status',
        'sepay_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sepay_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
