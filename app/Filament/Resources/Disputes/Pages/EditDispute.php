<?php

namespace App\Filament\Resources\Disputes\Pages;

use App\Filament\Resources\Disputes\DisputeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDispute extends EditRecord
{
    protected static string $resource = DisputeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action: Bắt đầu xử lý
            Action::make('resolving')
                ->label('Bắt đầu xử lý')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) && $this->record->status === 'pending')
                ->action(function () {
                    $this->record->update([
                        'status' => 'resolving',
                        'resolved_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Đã chuyển sang trạng thái đang xử lý')
                        ->success()
                        ->send();
                }),

            // Action: Hoàn tiền cho người mua (Hủy giao dịch)
            Action::make('refund_buyer')
                ->label('Hoàn tiền Buyer')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) && $this->record->status === 'resolving')
                ->requiresConfirmation()
                ->action(function () {
                    $dispute = $this->record;
                    $transaction = $dispute->transaction; // MorphTo

                    if (!$transaction) {
                         Notification::make()->title('Giao dịch không tồn tại')->danger()->send();
                         return;
                    }

                    \Illuminate\Support\Facades\DB::transaction(function () use ($dispute, $transaction) {
                        $buyerBalance = $transaction->buyer->balance;
                        
                        if ($transaction instanceof \App\Models\ShopTransaction) {
                            // Shop: Fee 1% is NOT refunded
                            $refundAmount = $transaction->amount - $transaction->fee;
                            $buyerBalance->decrement('held_balance', $transaction->amount);
                            $buyerBalance->increment('balance', $refundAmount);
                        } else {
                            // Middleman: Refund base amount, keep fee?
                            // Total held was amount + fee.
                            $totalHeld = $transaction->amount + $transaction->fee;
                            $buyerBalance->decrement('held_balance', $totalHeld);
                            $buyerBalance->increment('balance', $transaction->amount);
                        }

                        $transaction->update(['status' => 'cancelled']);
                        $dispute->update([
                            'status' => 'resolved',
                            'resolution' => 'Admin quyết định hoàn tiền cho người mua. Giao dịch bị hủy.',
                            'resolved_at' => now(),
                        ]);
                    });

                    Notification::make()->title('Đã hoàn tiền cho người mua')->success()->send();
                }),

            // Action: Chuyển tiền Seller (Hoàn tất giao dịch)
            Action::make('release_to_seller')
                ->label('Quyết cho Seller')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) && $this->record->status === 'resolving')
                ->requiresConfirmation()
                ->action(function () {
                    $dispute = $this->record;
                    $transaction = $dispute->transaction;

                    if (!$transaction) {
                         Notification::make()->title('Giao dịch không tồn tại')->danger()->send();
                         return;
                    }

                    \Illuminate\Support\Facades\DB::transaction(function () use ($dispute, $transaction) {
                        $buyerBalance = $transaction->buyer->balance;
                        $sellerBalance = $transaction->seller->balance;
                        
                        if ($transaction instanceof \App\Models\ShopTransaction) {
                            $netAmount = $transaction->amount - $transaction->fee;
                            $buyerBalance->decrement('held_balance', $transaction->amount);
                            $sellerBalance->increment('balance', $netAmount);
                        } else {
                            // Middleman
                            $totalHeld = $transaction->amount + $transaction->fee;
                            $buyerBalance->decrement('held_balance', $totalHeld);
                            $sellerBalance->increment('balance', $transaction->amount);
                        }

                        $transaction->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        $dispute->update([
                            'status' => 'resolved',
                            'resolution' => 'Admin quyết định chuyển tiền cho người bán. Giao dịch hoàn tất.',
                            'resolved_at' => now(),
                        ]);
                    });

                    Notification::make()->title('Đã chuyển tiền cho người bán')->success()->send();
                }),

            // Action: Hoàn thành giải quyết (Manual)
            Action::make('resolved')
                ->label('Hoàn thành giải quyết')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) && $this->record->status === 'resolving')
                ->form([
                    Textarea::make('resolution')
                        ->label('Kết quả giải quyết')
                        ->placeholder('Nhập chi tiết phương án giải quyết...')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'resolved',
                        'resolution' => $data['resolution'],
                        'resolved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Đã hoàn thành giải quyết tranh chấp')
                        ->success()
                        ->send();
                }),

            // Action: Hủy tranh chấp
            Action::make('cancelled')
                ->label('Hủy tranh chấp')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) && in_array($this->record->status, ['pending', 'resolving']))
                ->requiresConfirmation()
                ->modalHeading('Hủy tranh chấp?')
                ->modalDescription('Hành động này sẽ đóng tranh chấp mà không có phương án giải quyết.')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('Đã hủy tranh chấp')
                        ->info()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
