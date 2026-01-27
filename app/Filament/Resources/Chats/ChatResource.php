<?php

namespace App\Filament\Resources\Chats;

use App\Filament\Resources\Chats\Pages\CreateChat;
use App\Filament\Resources\Chats\Pages\EditChat;
use App\Filament\Resources\Chats\Pages\ListChats;
use App\Models\Chat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ChatResource extends Resource
{
    protected static ?string $model = Chat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống Chat';

    protected static ?string $navigationLabel = 'Phòng chat';

    protected static ?string $pluralLabel = 'Phòng chat';

    protected static ?string $modelLabel = 'Phòng chat';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Chats\Schemas\ChatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Chats\Tables\ChatsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->whereHas('participants', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChats::route('/'),
            'create' => CreateChat::route('/create'),
            'edit' => EditChat::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\MessagesRelationManager::class,
        ];
    }
}
