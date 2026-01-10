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
 * @property string $type
 * @property string $status
 */
class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'type', 'status'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
