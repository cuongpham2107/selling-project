<?php

namespace App\Filament\Resources\Deposits\Tables;

use App\Filament\Resources\Deposits\Actions\ApproveAction;
use App\Filament\Resources\Deposits\Enums\Method;
use App\Filament\Resources\Deposits\Enums\Status;
use App\Filament\Resources\Deposits\Filters\CustomDepositFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->orderByRaw("
                    CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'completed' THEN 2
                        WHEN 'failed' THEN 3
                        ELSE 4
                    END
                ")->orderBy('created_at', 'desc');
            })
            ->columns([
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->hidden(fn ($record) => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Số tiền')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Phương thức')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Method::BankTransfer->value => 'Chuyển khoản ngân hàng',
                        Method::CreditCard->value => 'Thẻ tín dụng',
                        Method::PayPal->value => 'PayPal',
                        default => $state,
                    })
                    ->width('250px')
                    ->disabled(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Status::Pending->value => 'Chờ duyệt',
                        Status::Completed->value => 'Thành công',
                        Status::Failed->value => 'Thất bại',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Status::Pending->value => 'gray',
                        Status::Completed->value => 'success',
                        Status::Failed->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Ngày nạp')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ApproveAction::make('approve')
                    ->hidden(fn ($record) => $record->status !== Status::Pending->value || ! auth()->user()->hasRole(config('filament-shield.super_admin.name'))),
                // EditAction::make()
                //     ->slideOver(),

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                CustomDepositFilter::make('deposit_filter'),
            ])
            ->defaultPaginationPageOption(25);
    }
}
