<?php

namespace App\Filament\Resources\ShopProducts\Pages;

use App\Filament\Resources\ShopProducts\ShopProductResource;
use App\Models\ShopProduct;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShopProducts extends ListRecords
{
    protected static string $resource = ShopProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('bulkUpload')
                ->label('Tải kho (.txt)')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Chọn file .txt')
                        ->acceptedFileTypes(['text/plain'])
                        ->disk('public')
                        ->directory('temp-uploads')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/public/'.$data['file']);

                    if (! file_exists($filePath)) {
                        return;
                    }

                    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $lines = array_slice($lines, 0, 5000);

                    $count = 0;
                    foreach ($lines as $line) {
                        $parts = array_map('trim', explode('|', $line));

                        // Basic logic:
                        // Part 1: Name (e.g. Account)
                        // All parts: Joined as description
                        // Last part (if numeric): Price?
                        // Let's assume a simpler format for now or just put the whole line in description

                        ShopProduct::create([
                            'user_id' => auth()->id(),
                            'name' => $parts[0] ?? 'Sản phẩm mới',
                            'description' => $line,
                            'price' => isset($parts[count($parts) - 1]) && is_numeric($parts[count($parts) - 1]) ? $parts[count($parts) - 1] : 0,
                            'stock' => 1,
                            'status' => 'available',
                        ]);
                        $count++;
                    }

                    \Illuminate\Support\Facades\Storage::disk('public')->delete($data['file']);

                    \Filament\Notifications\Notification::make()
                        ->title('Tải kho thành công')
                        ->body("Đã thêm $count sản phẩm vào hệ thống.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
