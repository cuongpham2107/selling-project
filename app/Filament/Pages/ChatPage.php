<?php

namespace App\Filament\Pages;

use App\Models\Message;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class ChatPage extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.chat-page'; // Non-static

    protected static ?string $title = 'Trò chuyện';

    protected static ?string $navigationLabel = 'Trò chuyện';

    protected static ?string $slug = 'chat';

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return 'full';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '';
    }

    public static function getNavigationBadge(): ?string
    {
        return Message::whereHas('chat.participants', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('sender_id', '!=', auth()->id()) // Not sent by me
            ->whereNull('read_at') // Not read yet
            ->count() ?: null;
        
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tin nhắn chưa đọc';
    }
}
