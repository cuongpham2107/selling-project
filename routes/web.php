<?php

use App\Models\Deposit;
use App\Services\SePayService;
use Illuminate\Support\Facades\Route;
use SePay\Builders\CheckoutBuilder;

Route::get('/sepay/redirect/{deposit}', function (Deposit $deposit) {
    $sepay = SePayService::getClient();

    $checkoutData = CheckoutBuilder::make()
        ->currency('VND')
        ->orderAmount((int) $deposit->amount)
        ->operation('PURCHASE')
        ->orderDescription('Nạp tiền vào ví - Đơn hàng #'.$deposit->id)
        ->orderInvoiceNumber($deposit->id)
        ->successUrl(route('filament.admin.resources.deposits.success', ['record' => $deposit->id]))
        ->cancelUrl(route('filament.admin.resources.deposits.index'))
        ->build();
    $formHtml = $sepay->checkout()->generateFormHtml($checkoutData);

    return view('sepay.checkout', compact('formHtml'));
})->name('sepay.redirect');
