<?php

namespace App\Livewire\Concerns;

use App\Models\Chat;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait HasChatContextMenu
{
    public $contextMenuUserId = null;

    public $contextMenuPosition = ['x' => 0, 'y' => 0];

    public $tempUserId = null;

    public function showContextMenu($userId, $x, $y): void
    {
        $this->contextMenuUserId = $userId;
        $this->contextMenuPosition = ['x' => $x, 'y' => $y];
    }

    public function hideContextMenu(): void
    {
        $this->contextMenuUserId = null;
    }

    public function viewProfile($userId): void
    {
        // TODO: Implement view profile logic
        $this->hideContextMenu();
        // You can redirect to user profile or show a modal
        // Example: $this->redirect(route('profile.show', $userId));
    }

    public function blockUser($userId): void
    {
        // TODO: Implement block user logic
        $this->hideContextMenu();
        // Example: Auth::user()->blockedUsers()->attach($userId);
    }

    public function reportUser($userId): void
    {
        // TODO: Implement report user logic
        $this->hideContextMenu();
        // Example: Show report modal or create a report
    }

    public function startPrivateChat($userId): void
    {
        $this->selectedUserId = $userId;
        $this->createNewChat();
        $this->hideContextMenu();
    }

    public function createMiddleTransactionAction(): Action
    {
        return Action::make('createMiddleTransaction')
            ->label('Tạo Giao Dịch Trung Gian')
            ->modalHeading('Tạo Giao Dịch Trung Gian Mới')
            ->modalDescription(function () {
                $user = User::query()->find($this->tempUserId ?? $this->contextMenuUserId);

                return $user ? "Tạo giao dịch với người dùng: {$user->name}" : 'Tạo giao dịch mới';
            })
            ->modalWidth('lg')
            ->before(function () {
                $this->hideContextMenu();
            })
            ->form([
                Select::make('role')
                    ->label('Vai Trò')
                    ->options([
                        'buyer' => 'Mua',
                        'seller' => 'Bán',
                    ])
                    ->required(),
                TextInput::make('amount')
                    ->label('Số Tiền')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('$')
                    ->helperText('Số tiền giao dịch'),

                TextInput::make('duration')
                    ->label('Thời Hạn (giờ)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(720)
                    ->default(24)
                    ->helperText('Thời gian hoàn thành giao dịch (tính bằng giờ)'),

                Textarea::make('description')
                    ->label('Mô Tả')
                    ->required()
                    ->maxLength(1000)
                    ->rows(4)
                    ->helperText('Mô tả chi tiết về giao dịch'),
            ])
            ->action(function (array $data) {
                $otherUserId = $this->tempUserId ?? $this->contextMenuUserId;
                $currentUserId = Auth::id();

                // Create new chat for this transaction
                $chat = Chat::create(['type' => 'private_transaction']);
                $chat->participants()->attach([$currentUserId, $otherUserId]);

                // Calculate fee
                $baseFee = Transaction::calculateBaseFee($data['amount']);
                // nếu role là buyer thì buyer_id là người dùng hiện tại, seller_id là người dùng khác
                // nếu role là seller thì seller_id là người dùng hiện tại, buyer_id là người dùng khác
                if ($data['role'] === 'buyer') {
                    $buyerId = $currentUserId;
                    $sellerId = $otherUserId;
                } else {
                    $buyerId = $otherUserId;
                    $sellerId = $currentUserId;
                }
                // Create transaction
                $transaction = Transaction::create([
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'description' => $data['description'],
                    'amount' => $data['amount'],
                    'duration' => $data['duration'],
                    'fee' => $baseFee,
                    'status' => 'pending',
                    'chat_id' => $chat->id,
                ]);

                // Select the chat
                $this->selectedChat = $chat->load(['participants', 'messages']);
                $this->dispatch('chat-selected');

                Notification::make()
                    ->title('Giao dịch đã được tạo thành công!')
                    ->success()
                    ->body("Giao dịch #{$transaction->id} - Số tiền: \${$data['amount']}")
                    ->send();

                $this->hideContextMenu();
            })
            ->after(function () {
                $this->hideContextMenu();
            })
            ->modalSubmitActionLabel('Tạo Giao Dịch')
            ->modalCancelActionLabel('Hủy')
            ->closeModalByClickingAway(false);
    }

    public function createMiddleTransaction($userId): void
    {
        $this->tempUserId = $userId;
        $this->hideContextMenu();
        $this->mountAction('createMiddleTransaction');
    }
}
