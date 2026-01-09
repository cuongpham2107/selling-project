<?php

namespace App\Filament\Resources\Points\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->hidden(fn ($record) => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points')
                    ->label('Số điểm')
                    ->sortable(),
            ])
            ->actions([
                Action::make('redeem')
                    ->label('Quy đổi VNĐ')
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn ($record) => $record->user->kyc_status === 'approved')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->label('Số điểm quy đổi')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->points),
                    ])
                    ->action(function ($record, array $data) {
                        $pointsToRedeem = $data['amount'];
                        $vndAmount = $pointsToRedeem * 500;

                        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $pointsToRedeem, $vndAmount) {
                            $record->decrement('points', $pointsToRedeem);
                            $record->user->balance->increment('balance', $vndAmount);

                            \App\Models\PointTransaction::create([
                                'user_id' => $record->user_id,
                                'amount' => $pointsToRedeem,
                                'type' => 'redeem',
                            ]);
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Quy đổi thành công')
                            ->body("Đã quy đổi $pointsToRedeem điểm thành ".number_format($vndAmount).' VNĐ.')
                            ->success()
                            ->send();
                    }),

                Action::make('send_points')
                    ->label('Gửi Point')
                    ->color('info')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        \Filament\Forms\Components\Select::make('recipient_id')
                            ->label('Người nhận')
                            ->options(\App\Models\User::where('id', '!=', auth()->id())->pluck('username', 'id'))
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->label('Số điểm gửi')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->points),
                    ])
                    ->action(function ($record, array $data) {
                        $amount = $data['amount'];
                        $recipientId = $data['recipient_id'];
                        $fee = ceil($amount * 0.005); // 0.5% fee
                        $totalDebit = $amount + $fee;

                        if ($record->points < $totalDebit) {
                            \Filament\Notifications\Notification::make()
                                ->title('Số dư không đủ')
                                ->body("Bạn cần ít nhất $totalDebit điểm (bao gồm $fee điểm phí gửi).")
                                ->danger()
                                ->send();

                            return;
                        }

                        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $recipientId, $amount, $totalDebit) {
                            // Debit sender
                            $record->decrement('points', $totalDebit);

                            // Credit recipient
                            $recipientPoint = \App\Models\Point::firstOrCreate(['user_id' => $recipientId], ['points' => 0]);
                            $recipientPoint->increment('points', $amount);

                            // Records
                            \App\Models\PointTransaction::create([
                                'user_id' => $record->user_id,
                                'amount' => $totalDebit,
                                'type' => 'send',
                                'recipient_id' => $recipientId,
                            ]);

                            \App\Models\PointTransaction::create([
                                'user_id' => $recipientId,
                                'amount' => $amount,
                                'type' => 'receive',
                                'sender_id' => $record->user_id,
                            ]);
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Gửi thành công')
                            ->body("Đã gửi $amount điểm cho người dùng.")
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
