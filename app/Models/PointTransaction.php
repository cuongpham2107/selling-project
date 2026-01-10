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
 * @property int $user_id
 * @property float $amount
 * @property string $type
 * @property int $related_id
 * @property string $related_type
 * @property int $recipient_id
 * @property int $sender_id
 */
class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'amount', 'type', 'related_id', 'related_type', 'recipient_id', 'sender_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
