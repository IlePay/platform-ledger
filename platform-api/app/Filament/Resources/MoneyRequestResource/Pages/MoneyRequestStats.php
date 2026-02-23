<?php

namespace App\Filament\Resources\MoneyRequestResource\Pages;

use App\Filament\Resources\MoneyRequestResource;
use Filament\Resources\Pages\Page;

class MoneyRequestStats extends Page
{
    protected static string $resource = MoneyRequestResource::class;
    protected static string $view = 'filament.pages.money-request-stats';
}
