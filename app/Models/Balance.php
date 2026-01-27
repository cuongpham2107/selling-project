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
 * @property float $balance
 * @property float $held_balance
 * @property-read \App\Models\User $user
 */
class Balance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'held_balance'];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
