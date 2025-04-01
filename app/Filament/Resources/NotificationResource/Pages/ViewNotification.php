<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Services\FirebaseNotificationService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification as FilamentNotification;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => !$record->is_sent),
            Actions\DeleteAction::make(),
            Actions\Action::make('send_now')
                ->label('Send Now')
                ->icon('heroicon-o-paper-airplane')
                ->action(function (FirebaseNotificationService $firebaseService) {
                    NotificationResource::sendNotificationAction($this->record, $firebaseService);
                    $this->refreshFormData(['is_sent', 'sent_at', 'scheduled_for']);
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn ($record) => !$record->is_sent),
        ];
    }
} 