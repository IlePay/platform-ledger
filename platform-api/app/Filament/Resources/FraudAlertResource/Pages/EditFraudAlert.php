<?php

namespace App\Filament\Resources\FraudAlertResource\Pages;

use App\Filament\Resources\FraudAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFraudAlert extends EditRecord
{
    protected static string $resource = FraudAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
