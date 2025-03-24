<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\NotificationRecipient;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;
    protected $recipientData = [
        'types' => [],
        'provinces' => [],
        'regions' => [],
        'communities' => [],
        'users' => [],
    ];
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('send_now')
                ->label('Send Now')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    
                    // Get all recipients for this notification
                    $recipients = $this->record->recipients;
                    $userIds = [];
                    
                    foreach ($recipients as $recipient) {
                        if ($recipient->recipient_type === 'user') {
                            $userIds[] = $recipient->recipient_id;
                        } elseif ($recipient->recipient_type === 'province') {
                            $provinceUserIds = \App\Models\User::whereHas('jesuit', function ($query) use ($recipient) {
                                $query->where('province_id', $recipient->recipient_id);
                            })->pluck('id')->toArray();
                            
                            $userIds = array_merge($userIds, $provinceUserIds);
                        } elseif ($recipient->recipient_type === 'region') {
                            $regionUserIds = \App\Models\User::whereHas('jesuit', function ($query) use ($recipient) {
                                $query->where('region_id', $recipient->recipient_id);
                            })->pluck('id')->toArray();
                            
                            $userIds = array_merge($userIds, $regionUserIds);
                        } elseif ($recipient->recipient_type === 'community') {
                            $communityUserIds = \App\Models\User::whereHas('jesuit', function ($query) use ($recipient) {
                                $query->where('current_community_id', $recipient->recipient_id);
                            })->pluck('id')->toArray();
                            
                            $userIds = array_merge($userIds, $communityUserIds);
                        } elseif ($recipient->recipient_type === 'all') {
                            $allUserIds = \App\Models\User::pluck('id')->toArray();
                            $userIds = array_merge($userIds, $allUserIds);
                        }
                    }
                    
                    // Remove duplicates
                    $userIds = array_unique($userIds);
                    
                    if (empty($userIds)) {
                        $this->notify('warning', 'No recipients found for this notification.');
                        return;
                    }
                    
                    $users = \App\Models\User::whereIn('id', $userIds)->get();
                    
                    // Send notification via Firebase FCM
                    $success = $firebaseService->sendToUsers($this->record, $users);
                    
                    if ($success) {
                        // Update notification status
                        $this->record->update([
                            'is_sent' => true,
                            'sent_at' => now(),
                        ]);
                        $this->notify('success', 'Notification sent successfully!');
                    } else {
                        $this->notify('danger', 'Failed to send notification.');
                    }
                })
                ->visible(fn ($record) => !$record->is_sent),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get recipient data for the form
        $recipientTypes = [];
        $provinceRecipients = [];
        $regionRecipients = [];
        $communityRecipients = [];
        $userRecipients = [];
        
        $recipients = NotificationRecipient::where('notification_id', $this->record->id)->get();
        
        foreach ($recipients as $recipient) {
            if ($recipient->recipient_type === 'all') {
                $recipientTypes[] = 'all';
            } else {
                $recipientTypes[] = $recipient->recipient_type;
                
                if ($recipient->recipient_type === 'province') {
                    $provinceRecipients[] = $recipient->recipient_id;
                } elseif ($recipient->recipient_type === 'region') {
                    $regionRecipients[] = $recipient->recipient_id;
                } elseif ($recipient->recipient_type === 'community') {
                    $communityRecipients[] = $recipient->recipient_id;
                } elseif ($recipient->recipient_type === 'user') {
                    $userRecipients[] = $recipient->recipient_id;
                }
            }
        }
        
        // Save for later use
        $this->recipientData = [
            'types' => $recipientTypes,
            'provinces' => $provinceRecipients,
            'regions' => $regionRecipients,
            'communities' => $communityRecipients,
            'users' => $userRecipients,
        ];
        
        // Add the recipient data to the form
        $data['recipient_types'] = array_unique($recipientTypes);
        $data['province_recipients'] = $provinceRecipients;
        $data['region_recipients'] = $regionRecipients;
        $data['community_recipients'] = $communityRecipients;
        $data['user_recipients'] = $userRecipients;
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract and remove recipient data to handle separately
        $this->recipientData = [
            'types' => $data['recipient_types'] ?? [],
            'provinces' => $data['province_recipients'] ?? [],
            'regions' => $data['region_recipients'] ?? [],
            'communities' => $data['community_recipients'] ?? [],
            'users' => $data['user_recipients'] ?? [],
        ];
        
        unset($data['recipient_types']);
        unset($data['province_recipients']);
        unset($data['region_recipients']);
        unset($data['community_recipients']);
        unset($data['user_recipients']);
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Delete all existing recipients
        NotificationRecipient::where('notification_id', $this->record->id)->delete();
        
        // Add new recipients based on the saved data
        if (in_array('all', $this->recipientData['types'])) {
            NotificationRecipient::create([
                'notification_id' => $this->record->id,
                'recipient_type' => 'all',
                'recipient_id' => null,
            ]);
        } else {
            if (in_array('province', $this->recipientData['types'])) {
                foreach ($this->recipientData['provinces'] as $provinceId) {
                    NotificationRecipient::create([
                        'notification_id' => $this->record->id,
                        'recipient_type' => 'province',
                        'recipient_id' => $provinceId,
                    ]);
                }
            }
            
            if (in_array('region', $this->recipientData['types'])) {
                foreach ($this->recipientData['regions'] as $regionId) {
                    NotificationRecipient::create([
                        'notification_id' => $this->record->id,
                        'recipient_type' => 'region',
                        'recipient_id' => $regionId,
                    ]);
                }
            }
            
            if (in_array('community', $this->recipientData['types'])) {
                foreach ($this->recipientData['communities'] as $communityId) {
                    NotificationRecipient::create([
                        'notification_id' => $this->record->id,
                        'recipient_type' => 'community',
                        'recipient_id' => $communityId,
                    ]);
                }
            }
            
            if (in_array('user', $this->recipientData['types'])) {
                foreach ($this->recipientData['users'] as $userId) {
                    NotificationRecipient::create([
                        'notification_id' => $this->record->id,
                        'recipient_type' => 'user',
                        'recipient_id' => $userId,
                    ]);
                }
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 