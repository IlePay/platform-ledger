<?php

namespace App\Filament\Resources\BlacklistResource\Pages;

use App\Filament\Resources\BlacklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlacklist extends CreateRecord
{
    protected static string $resource = BlacklistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['added_by'] = auth()->id();
        return $data;
    }
}
