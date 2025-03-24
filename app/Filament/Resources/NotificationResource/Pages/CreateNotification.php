<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\Event;
use App\Models\NotificationRecipient;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the creator
        $data['created_by'] = Auth::id();
        
        return $data;
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // Extract and remove recipient data
        $recipientTypes = $data['recipient_types'] ?? [];
        $provinceRecipients = $data['province_recipients'] ?? [];
        $regionRecipients = $data['region_recipients'] ?? [];
        $communityRecipients = $data['community_recipients'] ?? [];
        $userRecipients = $data['user_recipients'] ?? [];
        
        unset($data['recipient_types']);
        unset($data['province_recipients']);
        unset($data['region_recipients']);
        unset($data['community_recipients']);
        unset($data['user_recipients']);
        
        // Create notification
        $notification = static::getModel()::create($data);
        
        // Create notification recipients
        if (in_array('all', $recipientTypes)) {
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'recipient_type' => 'all',
                'recipient_id' => null,
            ]);
        }
        
        if (in_array('province', $recipientTypes)) {
            foreach ($provinceRecipients as $provinceId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'province',
                    'recipient_id' => $provinceId,
                ]);
            }
        }
        
        if (in_array('region', $recipientTypes)) {
            foreach ($regionRecipients as $regionId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'region',
                    'recipient_id' => $regionId,
                ]);
            }
        }
        
        if (in_array('community', $recipientTypes)) {
            foreach ($communityRecipients as $communityId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'community',
                    'recipient_id' => $communityId,
                ]);
            }
        }
        
        if (in_array('user', $recipientTypes)) {
            foreach ($userRecipients as $userId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'user',
                    'recipient_id' => $userId,
                ]);
            }
        }
        
        return $notification;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function mount(): void
    {
        parent::mount();
        
        // Check if there is an event_id in the URL parameters
        $eventId = request()->query('event_id');
        if ($eventId) {
            $event = Event::find($eventId);
            if ($event) {
                // Automatically populate fields based on the event
                $this->form->fill([
                    'title' => "Upcoming Event: {$event->title}",
                    'content' => "You are invited to attend {$event->title} on {$event->start_datetime->format('F j, Y, g:i a')} at " . 
                                ($event->venue ?? 'the designated venue') . ".\n\n{$event->description}",
                    'type' => 'event',
                    'event_id' => $eventId,
                    'scheduled_for' => $event->start_datetime->subDays(7), // One week before
                ]);
            }
        }
    }

    protected function getFormSchema(): array
    {
        $schema = parent::getFormSchema();
        
        // Check if there's an event_id in the URL
        $eventId = request()->query('event_id');
        if ($eventId) {
            // Find the event_id component and set its default value
            foreach ($schema as $index => $component) {
                if (method_exists($component, 'getName') && $component->getName() === 'event_id') {
                    $schema[$index] = $component->default($eventId);
                }
            }
        }
        
        return $schema;
    }
} 