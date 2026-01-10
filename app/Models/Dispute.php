<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model properties
 *
 * @property int $id
 * @property int $transaction_id
 * @property string $transaction_type
 * @property int $initiator_id
 * @property string $reason
 * @property string $status
 * @property int|null $resolved_by
 * @property string|null $resolution
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property-read mixed $transaction
 * @property-read \App\Models\User $initiator
 * @property-read \App\Models\User|null $resolver
 */
class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'transaction_type', 'initiator_id', 'reason',
        'status', 'resolved_by', 'resolution', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function transaction(): MorphTo
    {
        return $this->morphTo();
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
