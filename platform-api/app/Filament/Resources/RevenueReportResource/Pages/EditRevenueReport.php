<?php

namespace App\Filament\Resources\RevenueReportResource\Pages;

use App\Filament\Resources\RevenueReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevenueReport extends EditRecord
{
    protected static string $resource = RevenueReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
