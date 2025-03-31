<?php

namespace App\Filament\Resources\JesuitResource\Pages;

use App\Filament\Resources\JesuitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJesuits extends ListRecords
{
    protected static string $resource = JesuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 