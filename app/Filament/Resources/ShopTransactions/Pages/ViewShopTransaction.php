<?php

namespace App\Filament\Resources\ShopTransactions\Pages;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use App\Filament\Resources\ShopTransactions\ShopTransactionResource;
use App\Models\FeeTier;
use App\Models\PointTier;
use App\Models\PointTransaction;
use App\Models\ShopTransaction;
use App\Services\BalanceTransactionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewShopTransaction extends ViewRecord
{
    protected static string $resource = ShopTransactionResource::class;

    protected function getHeaderActions(): array
    {
        $currentUserId = auth()->id();

        return [
            // EditAction::make(),

            // SELLER: Confirm order and hold payment (pending → held)
            Action::make('confirm_order')
                ->label('Xác nhận đơn hàng')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Pending &&
                    $record->seller_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Xác nhận đơn hàng')
                ->modalDescription('Bạn có chắc chắn muốn xác nhận đơn hàng này? Tiền sẽ được giữ từ người mua.')
                ->action(function (ShopTransaction $record, Action $action) {
                    DB::transaction(function () use ($record) {
                        $buyerBalance = $record->buyer->balance;
                        $totalAmount = $record->amount;

                        // Check if buyer has enough balance
                        if ($buyerBalance->balance < $totalAmount) {
                            Notification::make()
                                ->title('Không đủ số dư')
                                ->body('Người mua không có đủ số dư để thực hiện giao dịch.')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Calculate and save fee for shop transaction (1% of amount)
                        $fee = FeeTier::calculateShopFee((float) $totalAmount);

                        // Move from available to held balance and record transaction
                        BalanceTransactionService::hold(
                            user: $record->buyer,
                            amount: $totalAmount,
                            type: 'hold',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Giữ tiền cho đơn hàng #'.$record->id,
                            metadata: [
                                'product_id' => $record->product_id,
                                'product_name' => $record->product->name,
                                'quantity' => $record->quantity,
                                'fee' => $fee,
                            ]
                        );

                        // Update transaction status and fee
                        $record->update([
                            'status' => Status::Held,
                            'fee' => $fee,
                        ]);

                        // Mark product as sold
                        $record->product->update(['status' => 'sold']);

                        Notification::make()
                            ->title('Đơn hàng đã được xác nhận')
                            ->body('Tiền đã được giữ từ người mua. Phí giao dịch: '.number_format($fee, 0, ',', '.').' VNĐ (1%)')
                            ->success()
                            ->send();
                    });

                    // Redirect to refresh and show product stock
                    $action->redirect(static::getUrl(['record' => $record->id]));
                }),

            // BUYER: Cancel order (pending → cancelled)
            Action::make('cancel_order')
                ->label('Hủy đơn hàng')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Pending &&
                    $record->buyer_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Hủy đơn hàng')
                ->modalDescription('Bạn có chắc chắn muốn hủy đơn hàng này?')
                ->action(function (ShopTransaction $record) {
                    // Update transaction status
                    // Product remains 'active' for other buyers
                    $record->update([
                        'status' => Status::Cancelled,
                        'cancelled_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Đơn hàng đã hủy')
                        ->body('Đơn hàng đã được hủy thành công.')
                        ->success()
                        ->send();
                }),

            // SELLER: Reject order (pending → cancelled)
            Action::make('reject_order')
                ->label('Từ chối đơn hàng')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Pending &&
                    $record->seller_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Từ chối đơn hàng')
                ->modalDescription('Bạn có chắc chắn muốn từ chối đơn hàng này?')
                ->action(function (ShopTransaction $record) {
                    // Update transaction status
                    // Product remains 'active' for other buyers
                    $record->update([
                        'status' => Status::Cancelled,
                        'cancelled_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Đơn hàng đã từ chối')
                        ->body('Đơn hàng đã được từ chối.')
                        ->warning()
                        ->send();
                }),

            // BUYER: Complete early (held → completed)
            Action::make('complete_early')
                ->label('Hoàn tất sớm')
                ->color('success')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Held &&
                    $record->buyer_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Hoàn tất giao dịch')
                ->modalDescription('Bạn xác nhận đã nhận được hàng và muốn giải phóng tiền cho người bán?')
                ->action(function (ShopTransaction $record) {
                    DB::transaction(function () use ($record) {
                        $totalAmount = $record->amount;

                        // Calculate fee for shop transaction (1% of amount)
                        $fee = FeeTier::calculateShopFee((float) $totalAmount);
                        $netAmount = $totalAmount - $fee;

                        // 1. Release held balance from buyer (full amount)
                        BalanceTransactionService::decrementHeldBalance(
                            user: $record->buyer,
                            amount: $totalAmount,
                            type: 'release',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Hoàn tất đơn hàng #'.$record->id
                        );

                        // 2. Record purchase for buyer (full amount paid)
                        BalanceTransactionService::record(
                            user: $record->buyer,
                            type: 'purchase',
                            amount: -$totalAmount,
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Mua hàng đơn #'.$record->id,
                            metadata: [
                                'product_name' => $record->product->name,
                                'amount' => $totalAmount,
                            ]
                        );

                        // 3. Transfer to seller (net amount after fee deduction)
                        BalanceTransactionService::incrementBalance(
                            user: $record->seller,
                            amount: $netAmount,
                            type: 'sale',
                            source: $record,
                            relatedUserId: $record->buyer_id,
                            description: 'Cộng tiền cho người bán hàng #'.$record->id,
                            metadata: [
                                'gross_amount' => $totalAmount,
                                'fee' => $fee,
                                'net_amount' => $netAmount,
                                'product_name' => $record->product->name,
                            ]
                        );

                        // 4. Record fee deduction from SELLER
                        BalanceTransactionService::record(
                            user: $record->seller,
                            type: 'fee',
                            amount: -$fee,
                            source: $record,
                            relatedUserId: $record->buyer_id,
                            description: 'Phí giao dịch đơn hàng #'.$record->id.' (1%)',
                            metadata: [
                                'fee_percentage' => 1,
                                'gross_amount' => $totalAmount,
                            ]
                        );

                        // Update transaction with calculated fee
                        $record->update([
                            'status' => Status::Completed,
                            'completed_at' => now(),
                            'fee' => $fee,
                        ]);

                        // Award Points to buyer and record point transaction
                        $points = PointTier::calculatePoints((float) $record->amount);
                        if ($points > 0) {
                            $record->buyer->point()->increment('points', $points);

                            // Record point earning in balance_transactions
                            BalanceTransactionService::record(
                                user: $record->buyer,
                                type: 'point_redeem',
                                amount: $points,
                                source: $record,
                                relatedUserId: $record->seller_id,
                                description: 'Nhận '.$points.' điểm từ đơn hàng #'.$record->id,
                                metadata: [
                                    'points_earned' => $points,
                                    'amount' => $totalAmount,
                                ]
                            );

                            PointTransaction::create([
                                'user_id' => $record->buyer_id,
                                'amount' => $points,
                                'type' => 'earn',
                                'related_id' => $record->id,
                                'related_type' => ShopTransaction::class,
                            ]);

                            // Referral reward
                            $referrer = $record->buyer->referredBy;
                            if ($referrer) {
                                $previousCount = \App\Models\Transaction::where('buyer_id', $record->buyer_id)
                                    ->where('status', 'completed')
                                    ->count() +
                                    ShopTransaction::where('buyer_id', $record->buyer_id)
                                        ->where('status', Status::Completed)
                                        ->where('id', '!=', $record->id)
                                        ->count();

                                if ($previousCount === 0) {
                                    $referrer->point()->increment('points', $points);

                                    // Record referral point
                                    BalanceTransactionService::record(
                                        user: $referrer,
                                        type: 'point_redeem',
                                        amount: $points,
                                        source: $record,
                                        relatedUserId: $record->buyer_id,
                                        description: 'Thưởng giới thiệu: '.$points.' điểm (100%)',
                                        metadata: [
                                            'points_earned' => $points,
                                            'referral_type' => 'first_transaction',
                                            'referred_user' => $record->buyer->username,
                                        ]
                                    );

                                    PointTransaction::create([
                                        'user_id' => $referrer->id,
                                        'amount' => $points,
                                        'type' => 'earn',
                                        'related_id' => $record->id,
                                        'related_type' => ShopTransaction::class,
                                        'recipient_id' => $record->buyer_id,
                                    ]);
                                } else {
                                    $recurringPoints = floor($points * 0.1);
                                    if ($recurringPoints > 0) {
                                        $referrer->point()->increment('points', $recurringPoints);

                                        // Record recurring referral point
                                        BalanceTransactionService::record(
                                            user: $referrer,
                                            type: 'point_redeem',
                                            amount: $recurringPoints,
                                            source: $record,
                                            relatedUserId: $record->buyer_id,
                                            description: 'Thưởng giới thiệu: '.$recurringPoints.' điểm (10%)',
                                            metadata: [
                                                'points_earned' => $recurringPoints,
                                                'referral_type' => 'recurring',
                                                'referred_user' => $record->buyer->username,
                                            ]
                                        );

                                        PointTransaction::create([
                                            'user_id' => $referrer->id,
                                            'amount' => $recurringPoints,
                                            'type' => 'earn',
                                            'related_id' => $record->id,
                                            'related_type' => ShopTransaction::class,
                                            'recipient_id' => $record->buyer_id,
                                        ]);
                                    }
                                }
                            }
                        }

                        Notification::make()
                            ->title('Giao dịch hoàn tất')
                            ->body('Tiền đã được chuyển cho người bán và điểm thưởng đã được cộng.')
                            ->success()
                            ->send();
                    });
                }),

            // BUYER: Initiate dispute (held → disputed)
            Action::make('dispute')
                ->label('Khiếu nại')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Held &&
                    $record->buyer_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Mở khiếu nại')
                ->modalDescription('Bạn có vấn đề với đơn hàng này? Nhân viên sẽ xem xét và hỗ trợ.')
                ->action(function (ShopTransaction $record) {
                    $record->update([
                        'status' => Status::Disputed,
                        'disputed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Đã mở khiếu nại')
                        ->body('Nhân viên sẽ hỗ trợ xử lý trong thời gian sớm nhất.')
                        ->warning()
                        ->send();
                }),

            // NGƯỜI BÁN: Hoàn thành yêu cầu (chỉ giữ, hành động thông báo)
            Action::make('request_completion')
                ->label('Yêu cầu hoàn tất')
                ->color('info')
                ->icon('heroicon-o-bell-alert')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Held &&
                    $record->seller_id === $currentUserId
                )
                ->requiresConfirmation()
                ->modalHeading('Yêu cầu người mua hoàn tất')
                ->modalDescription('Gửi thông báo nhắc nhở người mua xác nhận đã nhận hàng.')
                ->action(function (ShopTransaction $record) {
                    // TODO: Send notification to buyer
                    Notification::make()
                        ->title('Yêu cầu hoàn tất (Người bán nhắc nhở)')
                        ->body('Hãy kiểm tra lại sản phẩm và xác nhận đã nhận hàng.')
                        ->warning()
                        ->actions([
                            Action::make('view')
                                ->label('Xem chi tiết')
                                ->button()
                                ->url(route('filament.admin.resources.shop-transactions.view', $record->id)),
                        ])
                        ->sendToDatabase($record->buyer);
                }),

            // ADMIN: Giải quyết tranh chấp - Hoàn tất (disputed → completed)
            Action::make('resolve_complete')
                ->label('Giải quyết - Hoàn tất')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Disputed &&
                    auth()->user()->hasRole('super_admin')
                )
                ->requiresConfirmation()
                ->modalHeading('Giải quyết khiếu nại')
                ->modalDescription('Xác nhận giải quyết tranh chấp và hoàn tất giao dịch cho người bán.')
                ->action(function (ShopTransaction $record) {
                    DB::transaction(function () use ($record) {
                        $totalAmount = $record->amount;

                        // Calculate fee for shop transaction (1% of amount)
                        $fee = FeeTier::calculateShopFee((float) $totalAmount);
                        $netAmount = $totalAmount - $fee;

                        // 1. Release held balance from buyer (full amount)
                        BalanceTransactionService::decrementHeldBalance(
                            user: $record->buyer,
                            amount: $totalAmount,
                            type: 'release',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Admin giải quyết tranh chấp - Hoàn tất đơn hàng #'.$record->id
                        );

                        // 2. Record purchase for buyer (full amount paid)
                        BalanceTransactionService::record(
                            user: $record->buyer,
                            type: 'purchase',
                            amount: -$totalAmount,
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Mua hàng đơn #'.$record->id.' (Admin giải quyết)',
                            metadata: [
                                'product_name' => $record->product->name,
                                'amount' => $totalAmount,
                                'resolved_by_admin' => true,
                            ]
                        );

                        // 3. Transfer to seller (net amount after fee deduction)
                        BalanceTransactionService::incrementBalance(
                            user: $record->seller,
                            amount: $netAmount,
                            type: 'sale',
                            source: $record,
                            relatedUserId: $record->buyer_id,
                            description: 'Thu tiền từ đơn hàng #'.$record->id.' (Admin giải quyết)',
                            metadata: [
                                'gross_amount' => $totalAmount,
                                'fee' => $fee,
                                'net_amount' => $netAmount,
                                'product_name' => $record->product->name,
                                'resolved_by_admin' => true,
                            ]
                        );

                        // 4. Record fee deduction from SELLER
                        BalanceTransactionService::record(
                            user: $record->seller,
                            type: 'fee',
                            amount: -$fee,
                            source: $record,
                            relatedUserId: $record->buyer_id,
                            description: 'Phí giao dịch đơn hàng #'.$record->id.' (1%)',
                            metadata: [
                                'fee_percentage' => 1,
                                'gross_amount' => $totalAmount,
                                'resolved_by_admin' => true,
                            ]
                        );

                        // Update transaction with calculated fee
                        $record->update([
                            'status' => Status::Completed,
                            'completed_at' => now(),
                            'resolved_at' => now(),
                            'fee' => $fee,
                        ]);

                        Notification::make()
                            ->title('Tranh chấp đã giải quyết')
                            ->body('Giao dịch đã hoàn tất, tiền được chuyển cho người bán.')
                            ->success()
                            ->send();
                    });
                }),

            // ADMIN: Giải quyết tranh chấp - Hoàn tiền (tranh chấp → hủy)
            Action::make('resolve_refund')
                ->label('Giải quyết - Hoàn tiền')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Disputed &&
                    auth()->user()->hasRole('super_admin')
                )
                ->requiresConfirmation()
                ->modalHeading('Giải quyết khiếu nại')
                ->modalDescription('Xác nhận hoàn tiền cho người mua và hủy giao dịch.')
                ->action(function (ShopTransaction $record) {
                    DB::transaction(function () use ($record) {
                        $totalAmount = $record->amount;

                        // 1. Release held balance from buyer
                        BalanceTransactionService::decrementHeldBalance(
                            user: $record->buyer,
                            amount: $totalAmount,
                            type: 'release',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Admin giải quyết tranh chấp - Hoàn tiền đơn hàng #'.$record->id
                        );

                        // 2. Refund to buyer (return full amount to balance)
                        BalanceTransactionService::incrementBalance(
                            user: $record->buyer,
                            amount: $totalAmount,
                            type: 'refund',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Hoàn tiền đơn hàng #'.$record->id.' (Admin giải quyết)',
                            metadata: [
                                'product_name' => $record->product->name,
                                'amount' => $totalAmount,
                                'resolved_by_admin' => true,
                            ]
                        );

                        // Update transaction and restore product
                        $record->update([
                            'status' => Status::Cancelled,
                            'resolved_at' => now(),
                        ]);

                        $record->product->update(['status' => 'active']);

                        Notification::make()
                            ->title('Tranh chấp đã giải quyết')
                            ->body('Đã hoàn tiền cho người mua và hủy giao dịch.')
                            ->success()
                            ->send();
                    });
                }),
        ];
    }
}
