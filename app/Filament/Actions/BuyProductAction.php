<?php

namespace App\Filament\Actions;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use App\Models\Chat;
use App\Models\FeeTier;
use App\Models\PointTier;
use App\Models\ShopProduct;
use App\Models\ShopTransaction;
use App\Services\BalanceTransactionService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
            ->schema([
                TextInput::make('count')
                    ->label('Số lượng')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
            ])
            ->action(function (ShopProduct $record, Action $action, array $data) {
                $buyer = auth()->user();

                if (! $buyer) {
                    Notification::make()
                        ->title('Bạn cần đăng nhập để mua sản phẩm.')
                        ->danger()
                        ->send();

                    return;
                }

                $quantity = (int) ($data['count'] ?? 1);
                $totalAmount = (float) $record->price * $quantity;

                // Ensure buyer has enough balance for the total amount
                if ($buyer->balance && $buyer->balance->balance < $totalAmount) {
                    Notification::make()
                        ->title('Lỗi số dư')
                        ->body('Tài khoản của bạn không đủ '.number_format($totalAmount, 0, ',', '.').' VNĐ. Vui lòng nạp tiền để thực hiện giao dịch.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($buyer->id === $record->user_id) {
                    Notification::make()
                        ->title('Bạn không thể mua sản phẩm của chính mình.')
                        ->danger()
                        ->send();

                    return;
                }

                $availableStock = $record->stock ?? [];
                if (count($availableStock) < $quantity) {
                    Notification::make()
                        ->title('Lỗi số lượng')
                        ->body('Sản phẩm này chỉ còn '.count($availableStock).' sản phẩm hiện có.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($record->status !== 'active') {
                    Notification::make()
                        ->title('Sản phẩm này không còn khả dụng.')
                        ->danger()
                        ->send();

                    return;
                }

                // Create order without deducting money yet (pending status)
                $transaction = DB::transaction(function () use ($buyer, $record, $quantity, $totalAmount) {
                    $fee = FeeTier::calculateShopFee((float) $totalAmount);
                    $points = PointTier::calculatePoints((float) $totalAmount);
                    $availableStock = $record->stock ?? [];

                    // Take the specified number of items from the stock
                    $purchasedItems = array_slice($availableStock, 0, $quantity);
                    $remainingStock = array_slice($availableStock, $quantity);

                    // Update product stock and status
                    $record->stock = $remainingStock;
                    if (count($remainingStock) === 0) {
                        $record->status = 'sold';
                    }
                    $record->save();

                    // Create chat room for this transaction
                    $chat = Chat::create([
                        'type' => 'private_shop',
                    ]);

                    // Attach participants (buyer and seller) to the chat
                    $chat->participants()->attach([$record->user_id, $buyer->id]);

                    // Create transaction record
                    $transaction = ShopTransaction::create([
                        'buyer_id' => $buyer->id,
                        'seller_id' => $record->user_id,
                        'product_id' => $record->id,
                        'amount' => $totalAmount,
                        'fee' => $fee,
                        'status' => 'held',
                        'chat_id' => $chat->id,
                        'end_time' => now()->addDays(3),
                        'product_data' => $purchasedItems,
                    ]);

                    BalanceTransactionService::hold(
                        user: $buyer,
                        amount: $totalAmount,
                        type: 'hold',
                        source: $transaction,
                        relatedUserId: $record->user_id,
                        description: 'Giữ tiền cho đơn hàng #'.$transaction->id,
                        metadata: [
                            'product_id' => $record->id,
                            'product_name' => $record->name,
                            'quantity' => $quantity,
                            'fee' => $fee,
                        ]
                    );

                    // Logic thưởng điểm cho người mua và người giới thiệu
                    \App\Services\PointService::distributePointsForTransaction(
                        buyer: $buyer,
                        amount: $totalAmount,
                        source: $transaction,
                        sellerId: $record->user_id
                    );

                    return $transaction;
                });

                Notification::make()
                    ->title('Mua hàng thành công.')
                    ->body('Đã mua hàng thành công. Thông tin về sản phẩm có trong đơn hàng đã mua của bạn')
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->label('Xem chi tiết')
                            ->button()
                            ->url(route('filament.admin.resources.shop-transactions.view', $transaction->id)),
                    ])
                    ->send();

                // Dispatch events to update shopping cart and balance
                $action->getLivewire()->dispatch('cartUpdated');
                $action->getLivewire()->dispatch('balanceUpdated');
            });
    }
}
