<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelOverdueTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-overdue-transactions';

    protected $description = 'Cancel overdue transactions after 1 hour of grace period';

    public function handle()
    {
        $transactions = \App\Models\Transaction::query()->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'disputed')
            ->where('end_time', '<=', now()->subHour())
            ->get();

        foreach ($transactions as $transaction) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
                $buyerBalance = $transaction->buyer->balance;
                $totalAmount = $transaction->amount;

                // Refund money to buyer
                // Releasing from held balance
                if ($transaction->status !== 'pending') {
                    \App\Services\BalanceTransactionService::release(
                        user: $transaction->buyer,
                        amount: $totalAmount,
                        type: 'release',
                        source: $transaction,
                        relatedUserId: $transaction->seller_id,
                        description: 'Hoàn tiền giao dịch trung gian #'.$transaction->id.' (Giao dịch quá hạn)'
                    );
                }

                $transaction->status = 'overdue';
                $transaction->save();
            });

            $this->info("Cancelled overdue transaction ID: {$transaction->id}");
        }
    }
}
