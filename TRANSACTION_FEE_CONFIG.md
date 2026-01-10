# Transaction Fee Configuration Guide

## 📋 Tổng quan

Hệ thống sử dụng file config `config/transaction.php` để quản lý tất cả các loại phí giao dịch và cài đặt liên quan.

## 🏗️ Cấu trúc Config

### 1. **Shop Transaction Fee (Gian hàng cá nhân)**
```php
'shop_transaction_fee' => [
    'type' => 'percentage',  // 'percentage' hoặc 'fixed'
    'value' => 1,            // 1% cho percentage, hoặc số tiền cố định (VNĐ)
]
```

**Cách sử dụng:**
```php
use App\Models\FeeTier;

// Tính phí 1% cho đơn hàng 500,000 VNĐ
$fee = FeeTier::calculateShopFee(500000);
// Kết quả: 5,000 VNĐ
```

### 2. **Middle Transaction Fee (Giao dịch trung gian)**
```php
'middle_transaction_fee_type' => 'middle',
```

**Cách sử dụng:**
```php
// Phí cơ bản từ bảng fee_tiers
$baseFee = FeeTier::calculateFee(500000, 'middle');
// Kết quả: 10,000 VNĐ (từ bảng fee_tiers)

// Phí bao gồm cả phí theo ngày
$totalFee = FeeTier::calculateMiddleFee(500000, 48); // 48 giờ = 2 ngày
// Kết quả: 10,000 + (10,000 * 0.20 * 2) = 14,000 VNĐ
```

### 3. **Daily Fee Multiplier (Phí theo ngày)**
```php
'daily_fee_multiplier' => 0.20,      // 20% mỗi ngày
'daily_fee_threshold' => 24,         // Áp dụng từ 24 giờ trở lên
```

**Công thức:**
```
Phí theo ngày = Phí cơ bản × 20% × Số ngày
Tổng phí = Phí cơ bản + Phí theo ngày
```

**Ví dụ:**
- Giao dịch 500,000 VNĐ, 12 giờ: Phí = 10,000 VNĐ (không cộng thêm)
- Giao dịch 500,000 VNĐ, 1 ngày: Phí = 10,000 + (10,000 × 0.20 × 1) = 12,000 VNĐ
- Giao dịch 500,000 VNĐ, 7 ngày: Phí = 10,000 + (10,000 × 0.20 × 7) = 24,000 VNĐ

### 4. **Transaction Timeout Settings**
```php
'timeout' => [
    'shop_transaction_auto_complete' => 3,    // 3 ngày tự động hoàn tất
    'grace_period_after_expiry' => 1,         // 1 giờ grace period
    'chat_history_retention' => 7,            // 7 ngày lưu chat
]
```

### 5. **Transaction Limits**
```php
'limits' => [
    'max_images_per_user_per_day_per_transaction' => 3,  // 3 ảnh/người/ngày
    'max_image_size' => 1024,                             // 1MB
]
```

## 📊 So sánh 2 loại phí

### Shop Transaction (Gian hàng)
| Số tiền | Phí 1% | Người bán nhận |
|---------|--------|----------------|
| 50,000 VNĐ | 500 VNĐ | 49,500 VNĐ |
| 500,000 VNĐ | 5,000 VNĐ | 495,000 VNĐ |
| 5,000,000 VNĐ | 50,000 VNĐ | 4,950,000 VNĐ |

### Middle Transaction (Trung gian) - Phí bậc thang

| Khoảng tiền | Phí cơ bản | Ví dụ 1 ngày | Ví dụ 3 ngày |
|-------------|-----------|--------------|--------------|
| < 100k | 4,000 | 4,800 | 6,400 |
| 100k-200k | 6,000 | 7,200 | 9,600 |
| 200k-1M | 10,000 | 12,000 | 16,000 |
| 1M-2M | 16,000 | 19,200 | 25,600 |
| 2M-5M | 36,000 | 43,200 | 57,600 |
| 5M-10M | 66,000 | 79,200 | 105,600 |
| 10M-30M | 150,000 | 180,000 | 240,000 |
| > 30M | 300,000 | 360,000 | 480,000 |

## 🔧 Methods Available

### FeeTier Model Methods

```php
// 1. Tính phí shop (1%)
FeeTier::calculateShopFee(float $amount): float

// 2. Tính phí middle cơ bản (từ bảng)
FeeTier::calculateFee(float $amount, string $type = 'middle'): float

// 3. Tính phí theo ngày
FeeTier::calculateDailyFee(float $baseFee, int $durationHours): float

// 4. Tính tổng phí middle (bao gồm phí ngày)
FeeTier::calculateMiddleFee(float $amount, int $durationHours): float
```

## 💡 Ví dụ sử dụng trong Controller/Action

### Shop Transaction
```php
use App\Models\FeeTier;

// Khi buyer mua sản phẩm
$amount = $product->price;
$fee = FeeTier::calculateShopFee((float) $amount);
$netAmount = $amount - $fee;

// Sau 3 ngày, seller nhận: $netAmount
$sellerBalance->increment('balance', $netAmount);
```

### Middle Transaction
```php
use App\Models\FeeTier;

// Khi tạo giao dịch
$amount = $request->amount;
$durationHours = $request->duration; // từ form: 1h, 24h, 48h, etc.

$totalFee = FeeTier::calculateMiddleFee((float) $amount, $durationHours);

// Giữ tiền + phí từ buyer
$buyerBalance->decrement('balance', $amount + $totalFee);

// Khi hoàn tất, seller nhận: $amount (buyer đã trả phí)
$sellerBalance->increment('balance', $amount);
```

## ⚙️ Thay đổi Config

### Thay đổi phí shop từ 1% → 2%
```php
// config/transaction.php
'shop_transaction_fee' => [
    'type' => 'percentage',
    'value' => 2, // ← Thay đổi từ 1 sang 2
]
```

### Thay đổi phí theo ngày từ 20% → 30%
```php
'daily_fee_multiplier' => 0.30, // ← Thay đổi từ 0.20 sang 0.30
```

### Thay đổi sang phí cố định cho shop
```php
'shop_transaction_fee' => [
    'type' => 'fixed',      // ← Đổi từ percentage
    'value' => 5000,        // 5,000 VNĐ cố định
]
```

## 🚀 Best Practices

1. **Cache config trong production:**
   ```bash
   php artisan config:cache
   ```

2. **Luôn cast amount sang float:**
   ```php
   $fee = FeeTier::calculateShopFee((float) $amount);
   ```

3. **Log phí khi tính toán quan trọng:**
   ```php
   \Log::info('Transaction fee calculated', [
       'amount' => $amount,
       'fee' => $fee,
       'type' => 'shop',
   ]);
   ```

4. **Test kỹ trước khi deploy:**
   ```bash
   php artisan tinker
   >>> FeeTier::calculateShopFee(500000)
   >>> FeeTier::calculateMiddleFee(500000, 48)
   ```
