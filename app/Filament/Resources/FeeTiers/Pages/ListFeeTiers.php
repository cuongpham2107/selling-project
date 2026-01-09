<?php
namespace App\Filament\Resources\FeeTiers\Pages;
use App\Filament\Resources\FeeTiers\FeeTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeeTiers extends ListRecords
{
    protected static string $resource = FeeTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
