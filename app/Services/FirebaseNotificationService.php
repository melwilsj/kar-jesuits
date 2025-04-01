<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\FirebaseCloudMessaging;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class FirebaseNotificationService
{
    private $accessToken;
    private $projectId;
    private $httpClient;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $credentialsJsonString = config('services.firebase.credentials');

        if (empty($this->projectId)) {
            Log::error('Firebase Project ID is not configured in services.php or .env.');
            $this->accessToken = null;
            $this->httpClient = null;
            return;
        }

        if (empty($credentialsJsonString)) {
            Log::error('Firebase credentials are not configured in services.php or .env (FIREBASE_CREDENTIALS).');
            $this->accessToken = null;
            $this->httpClient = null;
            return;
        }

        try {
            $credentialsArray = json_decode($credentialsJsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode FIREBASE_CREDENTIALS JSON string: ' . json_last_error_msg());
                $this->accessToken = null;
                $this->httpClient = null;
                return;
            }

            if (!isset($credentialsArray['client_email'], $credentialsArray['private_key'])) {
                 Log::error('FIREBASE_CREDENTIALS JSON is missing required keys (client_email, private_key).');
                 $this->accessToken = null;
                 $this->httpClient = null;
                 return;
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $credentialsArray
            );

            $this->httpClient = HttpHandlerFactory::build();
            $token = $credentials->fetchAuthToken($this->httpClient);

            if (isset($token['access_token'])) {
                $this->accessToken = $token['access_token'];
            } else {
                Log::error('Failed to fetch Firebase access token using provided credentials.', ['token_response' => $token]);
                $this->accessToken = null;
                $this->httpClient = null;
            }

        } catch (\Exception $e) {
            Log::error('Failed to initialize FirebaseNotificationService or get access token: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->accessToken = null;
            $this->httpClient = null;
        }
    }

    public function sendNotification(Notification $notification, array $tokens)
    {
        if (empty($tokens) || empty($this->accessToken) || empty($this->httpClient)) {
             Log::warning('FirebaseNotificationService not properly initialized or no tokens provided. Skipping send.', [
                 'notification_id' => $notification->id,
                 'has_token' => !empty($this->accessToken),
                 'has_http_client' => !empty($this->httpClient),
                 'token_count' => count($tokens)
             ]);
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $messages = [];

        foreach ($tokens as $token) {
            if (empty($token) || !is_string($token)) {
                Log::warning('Invalid FCM token encountered, skipping.', ['notification_id' => $notification->id]);
                continue;
            }
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

        if (empty($messages)) {
            Log::info('No valid messages to send after filtering tokens.', ['notification_id' => $notification->id]);
            return false;
        }

        $successCount = 0;
        $totalMessages = count($messages);

        foreach ($messages as $index => $message) {
            $token = $message['message']['token'];
            try {
                $response = Http::withToken($this->accessToken)
                                ->withHeaders(['Content-Type' => 'application/json'])
                                ->post($url, $message);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    Log::error('FCM API error response:', [
                        'notification_id' => $notification->id,
                        'token_ending_in' => substr($token, -6),
                        'status_code' => $response->status(),
                        'response_body' => $response->json() ?? $response->body(),
                    ]);
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                 Log::error('HTTP Request Exception during FCM send:', [
                    'notification_id' => $notification->id,
                    'token_ending_in' => substr($token, -6),
                    'message' => $e->getMessage(),
                    'response_body' => $e->response->body() ?? null,
                 ]);
            } catch (\Exception $e) {
                Log::error('General Exception during FCM send HTTP request:', [
                    'notification_id' => $notification->id,
                    'token_ending_in' => substr($token, -6),
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }

        if ($successCount > 0) {
             Log::info("FCM Send Summary: {$successCount}/{$totalMessages} messages sent successfully.", ['notification_id' => $notification->id]);
        } else if ($totalMessages > 0) {
             Log::error("FCM Send Summary: Failed to send any messages ({$totalMessages} attempted).", ['notification_id' => $notification->id]);
        }

        return $successCount > 0;
    }

    public function sendToUsers(Notification $notification, $users)
    {
        $tokens = [];

        if (!is_iterable($users)) {
            Log::error('Invalid $users data provided to sendToUsers. Expected iterable.', ['notification_id' => $notification->id]);
            return false;
        }

        foreach ($users as $user) {
            if ($user instanceof User && !empty($user->fcm_tokens) && is_array($user->fcm_tokens)) {
                $validUserTokens = array_filter($user->fcm_tokens, fn($token) => !empty($token) && is_string($token));
                if (!empty($validUserTokens)) {
                    $tokens = array_merge($tokens, $validUserTokens);
                }
            }
        }

        $uniqueTokens = array_unique($tokens);

        if (empty($uniqueTokens)) {
             Log::info('No valid, unique FCM tokens found for any recipient.', ['notification_id' => $notification->id]);
             return false;
        }

        return $this->sendNotification($notification, $uniqueTokens);
    }
} 