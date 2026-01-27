<?php

namespace App\Filament\Resources\Deposits\Actions;

use App\Filament\Resources\Deposits\Enums\Status;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApproveAction
{
    public static function make(?string $name = null): Action
    {
        return Action::make($name)
            ->label('Chấp nhận')
            ->button()
            ->size('xs')
            ->color('blue')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->modalHeading('Xác nhận duyệt nạp tiền')
            ->modalDescription(fn (Model $record) => "Bạn có chắc chắn muốn duyệt khoản nạp {$record->amount} VNĐ cho người dùng {$record->user->username}?")
            ->modalSubmitActionLabel('Xác nhận')
            ->action(function (Model $record): void {
                DB::transaction(function () use ($record): void {
                    $user = $record->user;
                    $balance = $user->balance;

                    if ($balance) {
                        $balance->increment('balance', $record->amount);
                    } else {
                        $user->balance()->create([
                            'balance' => $record->amount,
                            'held_balance' => 0,
                        ]);
                    }

                    $record->update(['status' => Status::Completed->value]);

                    Notification::make()
                        ->title('Duyệt nạp tiền thành công')
                        ->body("Đã cộng {$record->amount} VNĐ vào tài khoản {$user->username}")
                        ->success()
                        ->send();
                });
            });
    }
}
