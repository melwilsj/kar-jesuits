<?php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommunity extends ViewRecord
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Standard Edit action
            Actions\DeleteAction::make(), // Add Delete action if appropriate
        ];
    }

    // No need to override mount() or other core methods unless you have
    // very specific custom logic. The base ViewRecord handles loading
    // the record and making it available to relation managers.
} 