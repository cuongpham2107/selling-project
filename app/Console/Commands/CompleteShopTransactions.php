<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompleteShopTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:complete-shop-transactions';

    protected $description = 'Complete shop transactions after 3 days of no dispute';

    public function handle()
    {
        $transactions = \App\Models\ShopTransaction::query()->where('status', \App\Filament\Resources\ShopTransactions\Enums\Status::Held)
            ->where('end_time', '<=', now())
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No held transactions found to complete.');

            return;
        }

        foreach ($transactions as $transaction) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
                $totalAmount = (float) $transaction->amount;
                $fee = (float) $transaction->fee;
                $netAmount = $totalAmount - $fee;

                // 1. Release held balance from buyer (full amount)
                \App\Services\BalanceTransactionService::decrementHeldBalance(
                    user: $transaction->buyer,
                    amount: $totalAmount,
                    type: 'release',
                    source: $transaction,
                    relatedUserId: $transaction->seller_id,
                    description: 'Tự động hoàn tất đơn hàng #'.$transaction->id.' (Đã qua thời gian khiếu nại)'
                );

                // 2. Record purchase for buyer (full amount paid)
                \App\Services\BalanceTransactionService::record(
                    user: $transaction->buyer,
                    type: 'purchase',
                    amount: -$totalAmount,
                    source: $transaction,
                    relatedUserId: $transaction->seller_id,
                    description: 'Mua hàng đơn #'.$transaction->id,
                    metadata: [
                        'product_name' => $transaction->product->name,
                        'amount' => $totalAmount,
                        'auto_completed' => true,
                    ]
                );

                // 3. Transfer to seller (net amount after fee deduction)
                \App\Services\BalanceTransactionService::incrementBalance(
                    user: $transaction->seller,
                    amount: $netAmount,
                    type: 'sale',
                    source: $transaction,
                    relatedUserId: $transaction->buyer_id,
                    description: 'Thu tiền từ đơn hàng #'.$transaction->id,
                    metadata: [
                        'gross_amount' => $totalAmount,
                        'fee' => $fee,
                        'net_amount' => $netAmount,
                        'product_name' => $transaction->product->name,
                        'auto_completed' => true,
                    ]
                );

                // 4. Record fee deduction from SELLER for tracking
                \App\Services\BalanceTransactionService::record(
                    user: $transaction->seller,
                    type: 'fee',
                    amount: -$fee,
                    source: $transaction,
                    relatedUserId: $transaction->buyer_id,
                    description: 'Phí giao dịch đơn hàng #'.$transaction->id,
                    metadata: [
                        'gross_amount' => $totalAmount,
                        'auto_completed' => true,
                    ]
                );

                $transaction->status = \App\Filament\Resources\ShopTransactions\Enums\Status::Completed;
                $transaction->completed_at = now();
                $transaction->save();
            });

            $this->info("Completed shop transaction ID: {$transaction->id}");
        }
    }
}
