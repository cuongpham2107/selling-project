<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\Dispute;
use App\Models\PointTransaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;

class InteractionSeeder extends Seeder
{
    public function run(): void
    {
        // Get ONLY panel_user role users (regular users)
        $regularUsers = User::role('panel_user')->get();
        
        if ($regularUsers->count() < 2) {
            $this->command->warn('⚠️  Not enough panel_user users to create interactions. Need at least 2 panel_user accounts.');
            return;
        }

        // 1. Create Transactions & Messages (Enhanced)
        for ($i = 0; $i < 5; $i++) {
            $buyer = $regularUsers->random();
            $seller = $regularUsers->where('id', '!=', $buyer->id)->random();

            $chat = Chat::create(['type' => 'private_middle']);
            $chat->participants()->attach([$buyer->id, $seller->id]);

            $transaction = Transaction::updateOrCreate(
                ['description' => "Giao dịch mua tài khoản Game #" . ($i + 1)],
                [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                    'amount' => rand(100000, 2000000),
                    'duration' => rand(1, 48),
                    'fee' => 20000,
                    'status' => ['pending', 'confirmed', 'completed', 'disputed'][rand(0, 3)],
                    'chat_id' => $chat->id,
                ]
            );

            Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $buyer->id,
                'content' => 'Chào bạn, mình muốn mua gói này. Bạn có online không?',
            ]);

            Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $seller->id,
                'content' => 'Chào bạn, mình có nhé. Bạn cứ tạo giao dịch đi.',
            ]);

            // 2. Create Disputes for some transactions
            if ($transaction->status === 'disputed' && !Dispute::where('transaction_id', $transaction->id)->exists()) {
                Dispute::create([
                    'transaction_type' => Transaction::class,
                    'transaction_id' => $transaction->id,
                    'initiator_id' => $buyer->id,
                    'reason' => 'Người bán không bàn giao thông tin đúng hạn sau khi mình đã thanh toán.',
                    'status' => 'pending',
                ]);
            }
        }

        // 3. Create Point Transactions
        foreach ($regularUsers as $user) {
            if (!PointTransaction::where('user_id', $user->id)->exists()) {
                PointTransaction::create([
                    'user_id' => $user->id,
                    'amount' => rand(10, 100),
                    'type' => 'earn',
                ]);

                PointTransaction::create([
                    'user_id' => $user->id,
                    'amount' => rand(5, 20),
                    'type' => 'redeem',
                ]);
            }
        }

        // 4. Create Deposits
        foreach ($regularUsers->take(3) as $user) {
            if (!Deposit::where('user_id', $user->id)->exists()) {
                Deposit::create([
                    'user_id' => $user->id,
                    'amount' => rand(500000, 5000000),
                    'method' => 'Chuyển khoản Vietcombank',
                    'status' => 'completed',
                ]);

                Deposit::create([
                    'user_id' => $user->id,
                    'amount' => 200000,
                    'method' => 'Momo',
                    'status' => 'pending',
                ]);
            }
        }

        // 5. Create Withdrawals
        foreach ($regularUsers->take(2) as $user) {
            if (!Withdrawal::where('user_id', $user->id)->exists()) {
                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => rand(100000, 1000000),
                    'type' => 'Vietinbank',
                    'status' => 'pending',
                ]);
            }
        }
    }
}
