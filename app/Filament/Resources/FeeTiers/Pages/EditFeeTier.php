<?php

namespace App\Filament\Resources\FeeTiers\Pages;

use App\Filament\Resources\FeeTiers\FeeTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeeTier extends EditRecord
{
    protected static string $resource = FeeTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
