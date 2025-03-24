<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('create_notification')
                ->label('Send Notification')
                ->icon('heroicon-o-bell')
                ->url(fn (): string => route('filament.resources.notifications.create', ['event_id' => $this->record->id]))
                ->openUrlInNewTab(),
        ];
    }
} 