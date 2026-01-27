<?php

namespace App\Filament\Resources\PointTiers\Pages;

use App\Filament\Resources\PointTiers\PointTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPointTier extends EditRecord
{
    protected static string $resource = PointTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
