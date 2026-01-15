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
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
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
