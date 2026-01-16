<?php

namespace App\Services;

use SePay\SePayClient;

class SePayService
{
    public static function getClient(): SePayClient
    {
        $config = [
            'timeout' => 60,
            'retry_attempts' => 5,
            'retry_delay' => 2000,
            'debug' => config('app.debug'),
            'user_agent' => 'MyApp/1.0 SePay-PHP-SDK/1.0.0',
        ];

        return new SePayClient(
            config('services.sepay.merchant_id'),
            config('services.sepay.api_key'),
            config('app.env') === 'production' ? SePayClient::ENVIRONMENT_PRODUCTION : SePayClient::ENVIRONMENT_SANDBOX,
            $config
        );
    }
}
