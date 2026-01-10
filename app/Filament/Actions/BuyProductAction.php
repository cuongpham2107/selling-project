<?php

namespace App\Filament\Actions;

use App\Models\Chat;
use App\Models\ShopProduct;
use App\Models\ShopTransaction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class BuyProductAction
{
   public static function make(): Action
    {
        return Action::make('buy_product')
            ->label('Mua ngay')
            ->color('primary')
            ->icon('heroicon-o-shopping-cart')
            ->button()
            ->size('lg')
            ->extraAttributes(['class' => 'w-full rounded-xl shadow-lg hover:shadow-success-500/30 transition-all'])
            ->requiresConfirmation()
            ->modalHeading('Xác nhận mua sản phẩm')
            ->modalDescription('Bạn có chắc chắn muốn mua sản phẩm này không?')
            ->action(function (ShopProduct $record, Action $action) {
                $buyer = auth()->user();

                if (! $buyer) {
                    Notification::make()
                        ->title('Bạn cần đăng nhập để mua sản phẩm.')
                        ->danger()
                        ->send();

                    return;
                }

                // Ensure buyer has a balance record
                if ($buyer->balance && $buyer->balance->balance < $record->price) {
                    Notification::make()
                        ->title('Lỗi số dư')
                        ->body('Tài khoản của bạn không đủ. Vui lòng nạp tiền vào tài khoản để thực hiện giao dịch.')
                        ->danger()
                        ->send();
                    return;
                }

                if($buyer->id === $record->user_id) {
                    Notification::make()
                        ->title('Bạn không thể mua sản phẩm của chính mình.')
                        ->danger()
                        ->send();
                    return;
                }

                if($record->status !== 'active')
                {
                    Notification::make()
                        ->title('Sản phẩm này không còn khả dụng.')
                        ->danger()
                        ->send();
                    return;
                }

                // Create order without deducting money yet (pending status)
                DB::transaction(function () use ($buyer, $record) {
                    // Product status remains 'active' until seller confirms
                    // This allows multiple buyers to attempt purchase
                    // First seller to confirm will get the sale
                    
                    // Create chat room for this transaction
                    $chat = Chat::create([
                        'type' => 'private_shop'
                    ]);
                    
                    // Attach participants (buyer and seller) to the chat
                    $chat->participants()->attach([$record->user_id, $buyer->id]);
                    
                    // Create transaction record with pending status
                    // Money will be deducted when seller confirms the order
                    ShopTransaction::create([
                        'buyer_id' => $buyer->id,
                        'seller_id' => $record->user_id,
                        'product_id' => $record->id,
                        'amount' => (float) $record->price,
                        'fee' => 0.00,
                        'status' => 'pending',
                        'chat_id' => $chat->id,
                        'end_time' => now()->addDays(3),
                        'completed_at' => null
                    ]);
                });

                Notification::make()
                    ->title('Đặt hàng thành công.')
                    ->body('Đã đặt hàng thành công. Đơn hàng đang chờ người bán xác nhận.')
                    ->success()
                    ->send();

                // Dispatch events to update shopping cart and balance
                $action->getLivewire()->dispatch('cartUpdated');
                $action->getLivewire()->dispatch('balanceUpdated');
            });
    }
}