<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Send all scheduled notifications that are due';

    public function handle()
    {
        $this->info('Looking for scheduled notifications...');
        
        // Get notifications that are scheduled for today or earlier and have not been sent yet
        $notifications = Notification::where('is_sent', false)
                                  ->whereNotNull('scheduled_for')
                                  ->where('scheduled_for', '<=', now())
                                  ->get();
        
        $this->info("Found {$notifications->count()} notifications to send.");
        
        if ($notifications->isEmpty()) {
            return 0;
        }
        
        $firebaseService = new FirebaseNotificationService();
        
        foreach ($notifications as $notification) {
            $this->info("Processing notification: {$notification->title}");
            
            // Get all recipients for this notification
            $recipients = $notification->recipients;
            $userIds = [];
            
            foreach ($recipients as $recipient) {
                if ($recipient->recipient_type === 'user') {
                    $userIds[] = $recipient->recipient_id;
                } elseif ($recipient->recipient_type === 'province') {
                    // Get all users in this province
                    $provinceUserIds = User::whereHas('jesuit', function ($query) use ($recipient) {
                        $query->where('province_id', $recipient->recipient_id);
                    })->pluck('id')->toArray();
                    
                    $userIds = array_merge($userIds, $provinceUserIds);
                } elseif ($recipient->recipient_type === 'region') {
                    // Get all users in this region
                    $regionUserIds = User::whereHas('jesuit', function ($query) use ($recipient) {
                        $query->where('region_id', $recipient->recipient_id);
                    })->pluck('id')->toArray();
                    
                    $userIds = array_merge($userIds, $regionUserIds);
                } elseif ($recipient->recipient_type === 'community') {
                    // Get all users in this community
                    $communityUserIds = User::whereHas('jesuit', function ($query) use ($recipient) {
                        $query->where('current_community_id', $recipient->recipient_id);
                    })->pluck('id')->toArray();
                    
                    $userIds = array_merge($userIds, $communityUserIds);
                } elseif ($recipient->recipient_type === 'all') {
                    // Get all users
                    $allUserIds = User::pluck('id')->toArray();
                    $userIds = array_merge($userIds, $allUserIds);
                }
            }
            
            // Remove duplicates
            $userIds = array_unique($userIds);
            
            if (empty($userIds)) {
                $this->warn("No recipients found for notification: {$notification->title}");
                continue;
            }
            
            $users = User::whereIn('id', $userIds)->get();
            
            // Send notification via Firebase FCM
            $success = $firebaseService->sendToUsers($notification, $users);
            
            if ($success) {
                // Update notification status
                $notification->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);
                $this->info("Notification sent successfully: {$notification->title}");
            } else {
                $this->error("Failed to send notification: {$notification->title}");
            }
        }
        
        return 0;
    }
} 