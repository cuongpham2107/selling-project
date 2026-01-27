<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Model properties
 *
 * @property int $id
 * @property int $chat_id
 * @property int $sender_id
 * @property string $content
 * @property string|null $image_url
 * @property int|null $product_id
 * @property \Illuminate\Support\Carbon|null $read_at
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id', 'sender_id', 'content', 'image_url', 'product_id', 'read_at', 'deleted_by_id', 'deleted_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_id');
    }

    public function isDeleted(): bool
    {
        return ! is_null($this->deleted_at);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // Helper methods
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    // Chat Constraints Logic
    public static function canSendMessage($user, $chat)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($chat->type === 'general') {
            // Check hour limit: 1 tin/giờ
            $hourCount = self::where('sender_id', $user->id)
                ->where('chat_id', $chat->id)
                ->where('created_at', '>=', now()->subHour())
                ->count();
            if ($hourCount >= 1) {
                return false;
            }

            // Check day limit: 3 tin/ngày
            $dayCount = self::where('sender_id', $user->id)
                ->where('chat_id', $chat->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            if ($dayCount >= 3) {
                return false;
            }
        }

        return true;
    }

    public static function canSendImage($user, $chat)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (in_array($chat->type, ['private_middle', 'private_shop'])) {
            // Giới hạn: 3 ảnh/người/ngày/giao dịch.
            $imageCount = self::where('sender_id', $user->id)
                ->where('chat_id', $chat->id)
                ->whereNotNull('image_url')
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            if ($imageCount >= 3) {
                return false;
            }
        }

        return true;
    }
}
