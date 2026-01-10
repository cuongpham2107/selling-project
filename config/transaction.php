<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình phí giao dịch
    |--------------------------------------------------------------------------
    |
    | File này chứa cấu hình phí cho toàn bộ hệ thống.
    | Phí được tính dựa trên số tiền giao dịch và loại giao dịch.
    |
    */

    /**
     * Cấu hình phí cho giao dịch gian hàng
     * Phí theo phần trăm hoặc cố định, cấu hình tại đây
     */
    'shop_transaction_fee' => [
        'type' => 'percentage', // percentage or fixed
        'value' => 1, // 1% for percentage, or fixed amount in VND
    ],

    /**
     * Hệ số phí theo ngày cho giao dịch >= 1 ngày
     * Thêm 20% phí cơ bản mỗi ngày cho giao dịch trung gian
     */
    'daily_fee_multiplier' => 0.20, // 20% per day

    /**
     * Ngưỡng thời gian để áp dụng phí theo ngày
     * Đơn vị: giờ
     */
    'daily_fee_threshold' => 24, // 24 hours = 1 day

    /**
     * Cấu hình thời gian cho giao dịch
     */
    'timeout' => [
        'shop_transaction_auto_complete' => 3, // days - auto complete if no dispute
        'grace_period_after_expiry' => 1, // hour - wait time before auto-cancel
        'chat_history_retention' => 7, // days - keep chat history after completion
    ],

    /**
     * Giới hạn liên quan đến giao dịch
     */
    'limits' => [
        'max_images_per_user_per_day_per_transaction' => 3,
        'max_image_size' => 1024, // KB (1MB)
    ],
];
