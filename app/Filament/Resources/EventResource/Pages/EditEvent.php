<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('create_notification')
                ->label('Send Notification')
                ->icon('heroicon-o-bell')
                ->url(function () {
                    // Use the correct route generation method from the NotificationResource
                    return NotificationResource::getUrl('create', [
                        'event_id' => $this->record->id
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 