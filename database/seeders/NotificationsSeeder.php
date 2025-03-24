<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Jesuit;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('email', 'melwilsj@jesuits.net')->first() ?? User::first();
        
        // Create notifications for upcoming events
        $upcomingEvents = Event::where('start_datetime', '>', now())
                               ->where('start_datetime', '<', now()->addMonths(1))
                               ->get();
        
        foreach ($upcomingEvents as $event) {
            $notification = Notification::create([
                'title' => "Upcoming Event: {$event->title}",
                'content' => "You are invited to attend {$event->title} on {$event->start_datetime->format('F j, Y, g:i a')} at " . 
                             ($event->venue ?? 'the designated venue') . ".\n\n{$event->description}",
                'type' => 'event',
                'event_id' => $event->id,
                'scheduled_for' => $event->start_datetime->subDays(7), // Notify one week before
                'is_sent' => false,
                'created_by' => $superadmin->id,
            ]);
            
            // Add recipients based on event scope
            if ($event->province_id) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'province',
                    'recipient_id' => $event->province_id,
                ]);
            }
            
            if ($event->region_id) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'region',
                    'recipient_id' => $event->region_id,
                ]);
            }
            
            if ($event->community_id) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'recipient_type' => 'community',
                    'recipient_id' => $event->community_id,
                ]);
            }
            
            // If it's a personal event (birthday, jubilee), add the Jesuit as recipient
            if ($event->jesuit_id) {
                $jesuit = Jesuit::find($event->jesuit_id);
                if ($jesuit && $jesuit->user_id) {
                    NotificationRecipient::create([
                        'notification_id' => $notification->id,
                        'recipient_type' => 'user',
                        'recipient_id' => $jesuit->user_id,
                    ]);
                }
            }
        }
        
        // Create some general announcement notifications
        $announcements = [
            'New Website Launched' => 'We are pleased to announce the launch of our new Jesuit Information System. Please update your profile information.',
            'Annual Reports Due' => 'A reminder that all annual reports are due by the end of the month. Please submit them promptly.',
            'New Provincial Appointed' => 'We are pleased to announce the appointment of a new Provincial Superior effective next month.',
            'Prayer Request' => 'Please keep our brothers in your prayers as they prepare for their final vows next week.',
        ];
        
        foreach ($announcements as $title => $content) {
            $notification = Notification::create([
                'title' => $title,
                'content' => $content,
                'type' => 'announcement',
                'is_sent' => true,
                'sent_at' => now()->subDays(rand(1, 30)),
                'created_by' => $superadmin->id,
            ]);
            
            // Make this a global announcement
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'recipient_type' => 'all',
                'recipient_id' => null,
            ]);
        }
    }
} 