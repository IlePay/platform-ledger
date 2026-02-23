<?php

namespace App\Filament\Resources\MoneyRequestResource\Pages;

use App\Filament\Resources\MoneyRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoneyRequest extends EditRecord
{
    protected static string $resource = MoneyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
