<?php

namespace App\Filament\Resources\Deposits\Pages;

use App\Filament\Resources\Deposits\DepositResource;
use App\Filament\Resources\Deposits\Enums\Status;
use App\Services\SePayService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class SuccessDeposit extends Page
{
    use InteractsWithRecord;

    protected static string $resource = DepositResource::class;

    protected string $view = 'filament.resources.deposits.pages.success-deposit';

    public ?array $orderData = null;

    public ?string $errorMessage = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        if ($this->record->status === Status::Completed->value) {
            return;
        }

        try {
            $sepay = SePayService::getClient();

            $order = $sepay->orders()->retrieve($this->record->id);
            $this->orderData = $order;

            DB::transaction(function () use ($order) {
                if ($order && isset($order['data']['order_status']) && $order['data']['order_status'] === 'CAPTURED') {
                    if ($this->record->status !== Status::Completed->value) {
                        $this->record->update([
                            'status' => Status::Completed->value,
                            'method' => $order['data']['transactions'][0]['transaction_type'] ?? null,
                            'sepay_payload' => $order,
                        ]);
                        $this->record->user->balance->increment('balance', $this->record->amount);
                    }
                }
            });

            if ($this->record->status === Status::Completed->value) {
                Notification::make()->title('Nạp tiền thành công')->success()->send();
            }

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Notification::make()->title('Nạp tiền thất bại')->body($this->errorMessage)->danger()->send();
        }
    }
}
