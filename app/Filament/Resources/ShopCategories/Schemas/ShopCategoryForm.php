<?php

namespace App\Filament\Resources\ShopCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ShopCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin danh mục')
                    ->description('Cấu hình tên, đường dẫn và mô tả cho danh mục sản phẩm.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Tên danh mục')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label('Đường dẫn (Slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icon (Heroicon)')
                                    ->placeholder('rectangle-stack'),
                                Toggle::make('is_active')
                                    ->label('Hiển thị')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
