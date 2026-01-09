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
        $transactions = \App\Models\ShopTransaction::where('status', 'held')
            ->where('created_at', '<=', now()->subDays(3))
            ->get();

        foreach ($transactions as $transaction) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
                $buyerBalance = $transaction->buyer->balance;
                $sellerBalance = $transaction->seller->balance;
                $totalAmount = $transaction->amount;
                $fee = $transaction->fee;
                $netAmount = $totalAmount - $fee;

                // Release from held balance
                $buyerBalance->decrement('held_balance', $totalAmount);

                // Transfer to seller
                $sellerBalance->increment('balance', $netAmount);

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            });

            $this->info("Completed shop transaction ID: {$transaction->id}");
        }
    }
}
