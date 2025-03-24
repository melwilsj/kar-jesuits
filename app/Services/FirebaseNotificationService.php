<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\FirebaseCloudMessaging;

class FirebaseNotificationService
{
    private $accessToken;
    private $projectId;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->accessToken = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $accessToken = $client->getAccessToken();
            
            return $accessToken['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase access token: ' . $e->getMessage());
            return null;
        }
    }

    public function sendNotification(Notification $notification, array $tokens)
    {
        if (empty($tokens) || empty($this->accessToken)) {
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $messages = [];

        foreach ($tokens as $token) {
            $messages[] = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notification->title,
                        'body' => strip_tags(substr($notification->content, 0, 200)),
                    ],
                    'data' => [
                        'notification_id' => (string) $notification->id,
                        'type' => $notification->type,
                        'event_id' => (string) ($notification->event_id ?? ''),
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'jesuit_info_system_channel',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];
        }

        $successCount = 0;
        
        foreach ($messages as $message) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ])->post($url, $message);
                
                if ($response->successful()) {
                    $successCount++;
                } else {
                    Log::error('FCM error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM notification error: ' . $e->getMessage());
            }
        }
        
        return $successCount > 0;
    }

    public function sendToUsers(Notification $notification, $users)
    {
        $tokens = [];
        
        foreach ($users as $user) {
            // Retrieve FCM tokens for this user
            if ($user->fcm_tokens) {
                $tokens = array_merge($tokens, $user->fcm_tokens);
            }
        }
        
        return $this->sendNotification($notification, array_unique($tokens));
    }
} 