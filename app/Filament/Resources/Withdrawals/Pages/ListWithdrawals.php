<?php

namespace App\Filament\Resources\Withdrawals\Pages;

use App\Filament\Resources\Withdrawals\WithdrawalResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawals extends ListRecords
{
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        // Check if user has default bank account
        $hasDefaultBankAccount = auth()->user()->defaultBankAccount !== null;

        return [
            CreateAction::make()
                ->label('Tạo yêu cầu rút tiền')
                ->slideOver()
                ->visible($hasDefaultBankAccount)
                ->before(function (CreateAction $action, array $data) {
                    $user = auth()->user();
                    $requestedAmount = (float) $data['amount'];

                    // Kiểm tra xem user có balance không
                    if (! $user->balance) {
                        Notification::make()
                            ->danger()
                            ->title('Không thể rút tiền')
                            ->body('Tài khoản của bạn chưa có ví. Vui lòng liên hệ hỗ trợ.')
                            ->persistent()
                            ->send();

                        $action->halt();
                    }

                    $availableBalance = (float) $user->balance->balance;

                    // Kiểm tra số dư có đủ không
                    if ($availableBalance <= 0) {
                        Notification::make()
                            ->danger()
                            ->title('Số dư không đủ')
                            ->body('Số dư khả dụng của bạn là 0 vnđ. Vui lòng nạp tiền trước khi rút.')
                            ->persistent()
                            ->send();

                        $action->halt();
                    }

                    if ($requestedAmount > $availableBalance) {
                        Notification::make()
                            ->danger()
                            ->title('Số dư không đủ')
                            ->body('Số dư khả dụng của bạn là '.number_format($availableBalance, 0, ',', '.').' vnđ. Bạn không thể rút '.number_format($requestedAmount, 0, ',', '.').' vnđ.')
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'pending';

                    return $data;
                })
                ->after(function ($record) {
                    // Gửi thông báo cho super admins
                    app(\App\Services\WithdrawalNotificationService::class)
                        ->notifySuperAdmins($record);
                }),
        ];
    }

    // public function mount(): void
    // {
    //     parent::mount();
    //     // Show notification if user doesn't have default bank account
    //     if (! auth()->user()->defaultBankAccount) {
    //         Notification::make()
    //             ->danger()
    //             ->title('Không có tài khoản ngân hàng mặc định')
    //             ->body('Vui lòng thêm và chọn một tài khoản ngân hàng mặc định để có thể tạo yêu cầu rút tiền.')
    //             ->persistent()
    //             ->send();
    //     }
    // }
}
