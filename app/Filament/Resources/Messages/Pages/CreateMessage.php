<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->data;
        $chat = \App\Models\Chat::find($data['chat_id']);
        $user = auth()->user();

        if ($chat && ! \App\Models\Message::canSendMessage($user, $chat)) {
            \Filament\Notifications\Notification::make()
                ->title('Giới hạn tin nhắn')
                ->body('Bạn đã đạt giới hạn gửi tin nhắn trong khung chat này (1 tin/giờ, 3 tin/ngày).')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($chat && isset($data['image_url']) && $data['image_url']) {
            if (! \App\Models\Message::canSendImage($user, $chat)) {
                \Filament\Notifications\Notification::make()
                    ->title('Giới hạn hình ảnh')
                    ->body('Bạn đã đạt giới hạn gửi hình ảnh (3 ảnh/ngày/giao dịch).')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }
}
