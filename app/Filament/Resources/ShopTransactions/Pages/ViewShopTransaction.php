<?php

namespace App\Filament\Resources\ShopTransactions\Pages;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use App\Filament\Resources\ShopTransactions\ShopTransactionResource;
use App\Models\FeeTier;
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
            // BUYER: Initiate dispute (held → disputed)
            Action::make('dispute')
                ->label('Khiếu nại')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (ShopTransaction $record) => $record->status === Status::Held &&
                    $record->buyer_id === $currentUserId
                )
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Lý do khiếu nại')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('Vui lòng mô tả chi tiết vấn đề của bạn với đơn hàng này...')
                        ->rows(4),
                ])
                ->modalHeading('Mở khiếu nại')
                ->modalDescription('Bạn có vấn đề với đơn hàng này? Nhân viên sẽ xem xét và hỗ trợ.')
                ->action(function (ShopTransaction $record, array $data) {
                    DB::transaction(function () use ($record, $data) {
                        // 1. Update transaction status
                        $record->update([
                            'status' => Status::Disputed,
                            'disputed_at' => now(),
                        ]);

                        // 2. Create Dispute record
                        \App\Models\Dispute::create([
                            'transaction_id' => $record->id,
                            'transaction_type' => ShopTransaction::class,
                            'initiator_id' => auth()->id(),
                            'reason' => $data['reason'],
                            'status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Đã mở khiếu nại')
                            ->body('Nhân viên sẽ hỗ trợ xử lý trong thời gian sớm nhất.')
                            ->warning()
                            ->send();
                    });
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
