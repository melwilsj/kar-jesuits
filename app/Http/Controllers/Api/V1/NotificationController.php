<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $user = $request->user();
        $jesuit = $user->jesuit ?? null;
        
        // Get notifications that have been sent
        $query = Notification::where('is_sent', true)
                           ->orderBy('sent_at', 'desc');
        
        // Join with notification_recipients to filter by recipient
        $query->where(function($q) use ($user, $jesuit) {
            // All notifications addressed to 'all'
            $q->whereHas('recipients', function($q2) {
                $q2->where('recipient_type', 'all');
            });
            
            // Notifications addressed directly to this user
            $q->orWhereHas('recipients', function($q2) use ($user) {
                $q2->where('recipient_type', 'user')
                   ->where('recipient_id', $user->id);
            });
            
            // If user is a Jesuit, include notifications for their province/region/community
            if ($jesuit) {
                if ($jesuit->province_id) {
                    $q->orWhereHas('recipients', function($q2) use ($jesuit) {
                        $q2->where('recipient_type', 'province')
                           ->where('recipient_id', $jesuit->province_id);
                    });
                }
                
                if ($jesuit->region_id) {
                    $q->orWhereHas('recipients', function($q2) use ($jesuit) {
                        $q2->where('recipient_type', 'region')
                           ->where('recipient_id', $jesuit->region_id);
                    });
                }
                
                if ($jesuit->current_community_id) {
                    $q->orWhereHas('recipients', function($q2) use ($jesuit) {
                        $q2->where('recipient_type', 'community')
                           ->where('recipient_id', $jesuit->current_community_id);
                    });
                }
            }
        });
        
        $notifications = $query->limit($limit)->get();
        
        // Get read status for each notification
        $readNotifications = NotificationRead::where('user_id', $user->id)
                                          ->whereIn('notification_id', $notifications->pluck('id'))
                                          ->pluck('notification_id')
                                          ->toArray();
        
        return response()->json([
            'success' => true,
            'data' => $notifications->map(function($notification) use ($readNotifications) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'type' => $notification->type,
                    'event_id' => $notification->event_id,
                    'sent_at' => $notification->sent_at->format('Y-m-d H:i:s'),
                    'is_read' => in_array($notification->id, $readNotifications),
                    'metadata' => $notification->metadata,
                    'event' => $notification->event ? [
                        'id' => $notification->event->id,
                        'title' => $notification->event->title,
                        'start_datetime' => $notification->event->start_datetime->format('Y-m-d H:i:s'),
                        'venue' => $notification->event->venue,
                    ] : null,
                ];
            }),
        ]);
    }
    
    /**
     * Get a specific notification
     */
    public function show(Notification $notification)
    {
        $user = request()->user();
        $jesuit = $user->jesuit ?? null;
        
        // Check if this notification is visible to the user
        $canView = false;
        
        foreach ($notification->recipients as $recipient) {
            if ($recipient->recipient_type === 'all') {
                $canView = true;
                break;
            }
            
            if ($recipient->recipient_type === 'user' && $recipient->recipient_id === $user->id) {
                $canView = true;
                break;
            }
            
            if ($jesuit) {
                if ($recipient->recipient_type === 'province' && $recipient->recipient_id === $jesuit->province_id) {
                    $canView = true;
                    break;
                }
                
                if ($recipient->recipient_type === 'region' && $recipient->recipient_id === $jesuit->region_id) {
                    $canView = true;
                    break;
                }
                
                if ($recipient->recipient_type === 'community' && $recipient->recipient_id === $jesuit->current_community_id) {
                    $canView = true;
                    break;
                }
            }
        }
        
        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this notification',
            ], 403);
        }
        
        // Check if notification is read
        $isRead = NotificationRead::where('notification_id', $notification->id)
                                ->where('user_id', $user->id)
                                ->exists();
        
        // Mark notification as read if not already read
        if (!$isRead) {
            NotificationRead::create([
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'read_at' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'type' => $notification->type,
                'event_id' => $notification->event_id,
                'sent_at' => $notification->sent_at->format('Y-m-d H:i:s'),
                'is_read' => true,
                'metadata' => $notification->metadata,
                'event' => $notification->event ? [
                    'id' => $notification->event->id,
                    'title' => $notification->event->title,
                    'description' => $notification->event->description,
                    'start_datetime' => $notification->event->start_datetime->format('Y-m-d H:i:s'),
                    'end_datetime' => $notification->event->end_datetime ? $notification->event->end_datetime->format('Y-m-d H:i:s') : null,
                    'venue' => $notification->event->venue,
                    'province' => $notification->event->province ? $notification->event->province->name : null,
                    'region' => $notification->event->region ? $notification->event->region->name : null,
                    'community' => $notification->event->community ? $notification->event->community->name : null,
                ] : null,
            ],
        ]);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $user = request()->user();
        
        // Check if already marked as read
        $exists = NotificationRead::where('notification_id', $notification->id)
                               ->where('user_id', $user->id)
                               ->exists();
        
        if (!$exists) {
            NotificationRead::create([
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'read_at' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
} 