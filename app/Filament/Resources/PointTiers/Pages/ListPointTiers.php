<?php
namespace App\Filament\Resources\PointTiers\Pages;
use App\Filament\Resources\PointTiers\PointTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPointTiers extends ListRecords
{
    protected static string $resource = PointTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
