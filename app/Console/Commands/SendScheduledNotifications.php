<?php

namespace App\Console\Commands;

use App\Models\Notification;
// Remove NotificationRecipient and User imports if not directly used elsewhere
// use App\Models\NotificationRecipient;
// use App\Models\User;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Send all scheduled notifications that are due';

    // Inject the service via the constructor
    protected FirebaseNotificationService $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $this->info('Looking for scheduled notifications...');

        // Get notifications that are scheduled for now or earlier and have not been sent yet
        $notifications = Notification::where('is_sent', false)
                                  ->whereNotNull('scheduled_for')
                                  ->where('scheduled_for', '<=', now())
                                  ->with('recipients') // Eager load recipients
                                  ->get();

        $this->info("Found {$notifications->count()} notifications to send.");

        if ($notifications->isEmpty()) {
            return 0;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($notifications as $notification) {
            $this->line("Processing notification ID: {$notification->id} - '{$notification->title}'");

            // Use the model method to get users
            $users = $notification->getRecipientUsers();

            if ($users->isEmpty()) {
                $this->warn("No active recipients found for notification ID: {$notification->id}. Marking as sent to prevent resending.");
                // Mark as sent even if no recipients to avoid retrying
                 $notification->update([
                     'is_sent' => true,
                     'sent_at' => now(),
                 ]);
                continue; // Skip sending if no users
            }

            $this->info("Attempting to send to {$users->count()} user(s)...");

            // Send notification via Firebase FCM using the injected service
            $success = $this->firebaseService->sendToUsers($notification, $users);

            if ($success) {
                // Update notification status
                $notification->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);
                $this->info("Notification ID: {$notification->id} sent successfully.");
                $sentCount++;
            } else {
                $this->error("Failed to send notification ID: {$notification->id}. Check logs.");
                // Don't update is_sent, it will be retried next time
                $failedCount++;
            }
        }

        $this->info("Finished processing. Sent: {$sentCount}, Failed: {$failedCount}.");
        return 0;
    }
} 