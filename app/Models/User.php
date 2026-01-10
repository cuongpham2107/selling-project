<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
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
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'referral_code',
        'referred_by',
        'kyc_status',
        'kyc_documents',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'kyc_documents' => 'json',
        ];
    }

    // ========================================
    // QUAN HỆ TÀI CHÍNH
    // ========================================

    /**
     * Lấy thông tin số dư ví của người dùng (quan hệ 1:1).
     * Bao gồm số dư khả dụng và số dư tạm giữ.
     */
    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class);
    }

    /**
     * Lấy thông tin điểm thưởng của người dùng (quan hệ 1:1).
     * Chứa tổng điểm loyalty/thưởng.
     */
    public function point(): HasOne
    {
        return $this->hasOne(Point::class);
    }

    /**
     * Lấy tất cả giao dịch điểm của người dùng.
     * Bao gồm: kiếm điểm (earn), đổi điểm (redeem), gửi điểm (send), nhận điểm (receive).
     */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Lấy tất cả yêu cầu nạp tiền của người dùng.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Lấy tất cả tài khoản ngân hàng của người dùng.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(UserBankAccount::class);
    }

    /**
     * Lấy tài khoản ngân hàng mặc định của người dùng.
     */
    public function defaultBankAccount(): HasOne
    {
        return $this->hasOne(UserBankAccount::class)->where('is_default', true);
    }

    /**
     * Lấy tất cả yêu cầu rút tiền của người dùng.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    // ========================================
    // QUAN HỆ GIỚI THIỆU
    // ========================================

    /**
     * Lấy thông tin người đã giới thiệu user này (người giới thiệu cấp trên).
     */
    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Lấy tất cả người dùng được giới thiệu bởi user này (F1 downline).
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // ========================================
    // QUAN HỆ CỬA HÀNG
    // ========================================

    /**
     * Lấy tất cả sản phẩm do người dùng này tạo/sở hữu.
     */
    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    /**
     * Lấy tất cả đơn hàng mà người dùng này là người MUA.
     */
    public function shopTransactionsAsBuyer(): HasMany
    {
        return $this->hasMany(ShopTransaction::class, 'buyer_id');
    }

    /**
     * Lấy tất cả đơn hàng mà người dùng này là người BÁN.
     */
    public function shopTransactionsAsSeller(): HasMany
    {
        return $this->hasMany(ShopTransaction::class, 'seller_id');
    }

    // ========================================
    // QUAN HỆ GIAO DỊCH TRUNG GIAN
    // ========================================

    /**
     * Lấy tất cả giao dịch trung gian mà người dùng này là người MUA.
     */
    public function transactionsAsBuyer(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /**
     * Lấy tất cả giao dịch trung gian mà người dùng này là người BÁN.
     */
    public function transactionsAsSeller(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    // ========================================
    // QUAN HỆ GIAO TIẾP
    // ========================================

    /**
     * Lấy tất cả phòng chat mà người dùng này tham gia (quan hệ nhiều-nhiều).
     */
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants');
    }

    /**
     * Lấy tất cả tin nhắn do người dùng này gửi.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // ========================================
    // QUAN HỆ TRANH CHẤP
    // ========================================

    /**
     * Lấy tất cả tranh chấp do người dùng này khởi tạo/tạo.
     */
    public function disputesInitiated(): HasMany
    {
        return $this->hasMany(Dispute::class, 'initiator_id');
    }

    /**
     * Lấy tất cả tranh chấp đã được giải quyết bởi người dùng này (chỉ admin/support).
     */
    public function disputesResolved(): HasMany
    {
        return $this->hasMany(Dispute::class, 'resolved_by');
    }

    // ========================================
    // QUAN HỆ LỊCH SỬ GIAO DỊCH
    // ========================================

    /**
     * Lấy tất cả các giao dịch balance của người dùng này.
     * Bao gồm: deposit, withdrawal, purchase, sale, point_redeem, etc.
     */
    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    /**
     * Get the user's name (mapped to username).
     */
    protected function getNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Set the user's name (mapped to username).
     */
    protected function setNameAttribute(string $value): void
    {
        $this->username = $value;
    }
}
