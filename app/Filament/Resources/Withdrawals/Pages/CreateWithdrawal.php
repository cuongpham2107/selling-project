<?php

namespace App\Filament\Resources\Withdrawals\Pages;

use App\Filament\Resources\Withdrawals\WithdrawalResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateWithdrawal extends CreateRecord
{
    protected static string $resource = WithdrawalResource::class;

    /**
     * Kiểm tra số dư trước khi tạo yêu cầu rút tiền.
     */
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $user = auth()->user();
    //     $requestedAmount = (float) $data['amount'];

    //     // Kiểm tra xem user có balance không
    //     if (! $user->balance) {
    //         Notification::make()
    //             ->danger()
    //             ->title('Không thể rút tiền')
    //             ->body('Tài khoản của bạn chưa có ví. Vui lòng liên hệ hỗ trợ.')
    //             ->persistent()
    //             ->send();

    //         $this->halt();
    //     }

    //     $availableBalance = (float) $user->balance->balance;

    //     // Kiểm tra số dư có đủ không
    //     if ($availableBalance <= 0) {
    //         Notification::make()
    //             ->danger()
    //             ->title('Số dư không đủ')
    //             ->body('Số dư khả dụng của bạn là 0 vnđ. Vui lòng nạp tiền trước khi rút.')
    //             ->persistent()
    //             ->send();

    //         $this->halt();
    //     }

    //     if ($requestedAmount > $availableBalance) {
    //         Notification::make()
    //             ->danger()
    //             ->title('Số dư không đủ')
    //             ->body('Số dư khả dụng của bạn là '.number_format($availableBalance, 0, ',', '.').' vnđ. Bạn không thể rút '.number_format($requestedAmount, 0, ',', '.').' vnđ.')
    //             ->persistent()
    //             ->send();

    //         $this->halt();
    //     }

    //     // Tự động gán user_id và status
    //     $data['user_id'] = $user->id;
    //     $data['status'] = 'pending';

    //     return $data;
    // }
}
