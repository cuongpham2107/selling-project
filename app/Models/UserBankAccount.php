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
 * @property string $bank_name
 * @property string $account_holder_name
 * @property string $account_number
 * @property bool $is_default
 */
class UserBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_holder_name',
        'account_number',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method để tự động set is_default
     */
    protected static function boot()
    {
        parent::boot();

        // Khi tạo tài khoản mới và set is_default = true
        static::creating(function ($bankAccount) {
            if ($bankAccount->is_default) {
                // Bỏ default của các tài khoản khác
                static::where('user_id', $bankAccount->user_id)
                    ->update(['is_default' => false]);
            }
        });

        // Khi update tài khoản và set is_default = true
        static::updating(function ($bankAccount) {
            if ($bankAccount->is_default && $bankAccount->isDirty('is_default')) {
                // Bỏ default của các tài khoản khác
                static::where('user_id', $bankAccount->user_id)
                    ->where('id', '!=', $bankAccount->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
