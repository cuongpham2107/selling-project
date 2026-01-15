<?php

namespace App\Filament\Resources\Users\Tables;

use App\Services\BalanceTransactionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('Tên đăng nhập')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('SĐT')
                    ->searchable(),
                TextColumn::make('balance.balance')
                    ->label('Số dư')
                    ->alignCenter()
                    ->money('VND')
                    ->searchable(),
                TextColumn::make('balance.held_balance')
                    ->label('Số dư giữ lại')
                    ->alignCenter()
                    ->money('VND')
                    ->searchable(),
                // Point
                TextColumn::make('point.points')
                    ->label('Điểm')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Vai trò')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'support_admin' => 'Support Admin',
                        'censor_staff' => 'NV Kiểm duyệt',
                        'censor' => 'Kiểm duyệt viên',
                        'user' => 'Người dùng',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'support_admin' => 'warning',
                        'censor_staff', 'censor' => 'info',
                        'user' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Đã duyệt',
                        'pending' => 'Chờ duyệt',
                        'rejected' => 'Từ chối',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('referral_code')
                    ->label('Mã REF')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('topup')
                    ->label('Cấp tiền')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->form([
                        TextInput::make('amount')
                            ->label('Số tiền cấp')
                            ->numeric()
                            ->required()
                            ->minValue(1000)
                            ->prefix('VNĐ')
                            ->placeholder('Nhập số tiền muốn cấp')
                            ->helperText('Số tiền tối thiểu: 1.000 VNĐ'),
                        Textarea::make('reason')
                            ->label('Lý do')
                            ->required()
                            ->placeholder('Nhập lý do cấp tiền (VD: Khuyến mãi, Bồi thường, Hỗ trợ,...)')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record) {
                        DB::transaction(function () use ($data, $record) {
                            $amount = (float) $data['amount'];
                            $reason = $data['reason'];

                            // Increment user balance
                            BalanceTransactionService::incrementBalance(
                                user: $record,
                                amount: $amount,
                                type: 'admin_topup',
                                source: null,
                                relatedUserId: auth()->id(),
                                description: 'Admin cấp tiền: '.$reason,
                                metadata: [
                                    'admin_id' => auth()->id(),
                                    'admin_username' => auth()->user()->username,
                                    'reason' => $reason,
                                    'amount' => $amount,
                                ]
                            );

                            Notification::make()
                                ->title('Cấp tiền thành công')
                                ->body('Đã cấp '.number_format($amount).' VNĐ cho '.$record->username)
                                ->success()
                                ->send();
                        });
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
