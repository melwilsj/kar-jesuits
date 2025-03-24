<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
} 