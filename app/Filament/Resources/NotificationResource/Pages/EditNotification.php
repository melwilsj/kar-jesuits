<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\NotificationRecipient;
use App\Services\FirebaseNotificationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification as FilamentNotification;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $recipientTypes = $data['recipient_types'] ?? [];
        $provinceRecipients = $data['province_recipients'] ?? [];
        $regionRecipients = $data['region_recipients'] ?? [];
        $communityRecipients = $data['community_recipients'] ?? [];
        $userRecipients = $data['user_recipients'] ?? [];

        $record->fill(collect($data)->except([
            'recipient_types', 'province_recipients', 'region_recipients', 'community_recipients', 'user_recipients'
        ])->all())->save();

        $record->recipients()->delete();

        $recipientsToCreate = [];
        if (in_array('all', $recipientTypes)) {
            $recipientsToCreate[] = ['recipient_type' => 'all', 'recipient_id' => null];
        } else {
            if (in_array('province', $recipientTypes)) {
                foreach ($provinceRecipients as $id) {
                    $recipientsToCreate[] = ['recipient_type' => 'province', 'recipient_id' => $id];
                }
            }
            if (in_array('region', $recipientTypes)) {
                foreach ($regionRecipients as $id) {
                    $recipientsToCreate[] = ['recipient_type' => 'region', 'recipient_id' => $id];
                }
            }
            if (in_array('community', $recipientTypes)) {
                foreach ($communityRecipients as $id) {
                    $recipientsToCreate[] = ['recipient_type' => 'community', 'recipient_id' => $id];
                }
            }
            if (in_array('user', $recipientTypes)) {
                foreach ($userRecipients as $id) {
                    $recipientsToCreate[] = ['recipient_type' => 'user', 'recipient_id' => $id];
                }
            }
        }

        if (!empty($recipientsToCreate)) {
            $record->recipients()->createMany($recipientsToCreate);
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $notification = static::getResource()::getModel()::find($data['id']);
        if ($notification) {
            $recipients = $notification->recipients()->get();
            $data['recipient_types'] = $recipients->pluck('recipient_type')->unique()->toArray();
            $data['province_recipients'] = $recipients->where('recipient_type', 'province')->pluck('recipient_id')->toArray();
            $data['region_recipients'] = $recipients->where('recipient_type', 'region')->pluck('recipient_id')->toArray();
            $data['community_recipients'] = $recipients->where('recipient_type', 'community')->pluck('recipient_id')->toArray();
            $data['user_recipients'] = $recipients->where('recipient_type', 'user')->pluck('recipient_id')->toArray();
        }
        return $data;
    }
} 