<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get upcoming events
     */
    public function upcoming(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user = $request->user();
        $jesuit = $user->jesuit ?? null;
        
        $query = Event::where('start_datetime', '>', now())
                     ->where('is_public', true)
                     ->orderBy('start_datetime', 'asc');
        
        // Add filters for user's province/region/community if available
        if ($jesuit) {
            $query->where(function($q) use ($jesuit) {
                $q->whereNull('province_id')
                  ->whereNull('region_id')
                  ->whereNull('community_id')
                  ->orWhere('province_id', $jesuit->province_id)
                  ->orWhere('region_id', $jesuit->region_id)
                  ->orWhere('community_id', $jesuit->current_community_id);
                  
                // Add personal events for this jesuit
                if ($jesuit->id) {
                    $q->orWhere('jesuit_id', $jesuit->id);
                }
            });
        }
        
        $events = $query->limit($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $events->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'type' => $event->type,
                    'event_type' => $event->event_type,
                    'start_datetime' => $event->start_datetime->format('Y-m-d H:i:s'),
                    'end_datetime' => $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null,
                    'venue' => $event->venue,
                    'province' => $event->province ? $event->province->name : null,
                    'region' => $event->region ? $event->region->name : null,
                    'community' => $event->community ? $event->community->name : null,
                    'attachments' => $event->attachments->map(function($attachment) {
                        return [
                            'id' => $attachment->id,
                            'type' => $attachment->type,
                            'file_name' => $attachment->file_name,
                            'caption' => $attachment->caption,
                            'url' => url('storage/' . $attachment->file_path),
                        ];
                    }),
                ];
            }),
        ]);
    }
    
    /**
     * Get past events
     */
    public function past(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user = $request->user();
        $jesuit = $user->jesuit ?? null;
        
        $query = Event::where('start_datetime', '<', now())
                     ->where('is_public', true)
                     ->orderBy('start_datetime', 'desc');
        
        // Add filters for user's province/region/community if available
        if ($jesuit) {
            $query->where(function($q) use ($jesuit) {
                $q->whereNull('province_id')
                  ->whereNull('region_id')
                  ->whereNull('community_id')
                  ->orWhere('province_id', $jesuit->province_id)
                  ->orWhere('region_id', $jesuit->region_id)
                  ->orWhere('community_id', $jesuit->current_community_id);
                  
                // Add personal events for this jesuit
                if ($jesuit->id) {
                    $q->orWhere('jesuit_id', $jesuit->id);
                }
            });
        }
        
        $events = $query->limit($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $events->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'type' => $event->type,
                    'event_type' => $event->event_type,
                    'start_datetime' => $event->start_datetime->format('Y-m-d H:i:s'),
                    'end_datetime' => $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null,
                    'venue' => $event->venue,
                    'province' => $event->province ? $event->province->name : null,
                    'region' => $event->region ? $event->region->name : null,
                    'community' => $event->community ? $event->community->name : null,
                    'attachments' => $event->attachments->map(function($attachment) {
                        return [
                            'id' => $attachment->id,
                            'type' => $attachment->type,
                            'file_name' => $attachment->file_name,
                            'caption' => $attachment->caption,
                            'url' => url('storage/' . $attachment->file_path),
                        ];
                    }),
                ];
            }),
        ]);
    }
    
    /**
     * Get a specific event
     */
    public function show(Event $event)
    {
        $user = request()->user();
        $jesuit = $user->jesuit ?? null;
        
        // Check if this event is visible to the user
        if (!$event->is_public) {
            if (!$jesuit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this event',
                ], 403);
            }
            
            $hasAccess = false;
            
            if ($event->province_id && $event->province_id === $jesuit->province_id) {
                $hasAccess = true;
            }
            
            if ($event->region_id && $event->region_id === $jesuit->region_id) {
                $hasAccess = true;
            }
            
            if ($event->community_id && $event->community_id === $jesuit->current_community_id) {
                $hasAccess = true;
            }
            
            if ($event->jesuit_id && $event->jesuit_id === $jesuit->id) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this event',
                ], 403);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'type' => $event->type,
                'event_type' => $event->event_type,
                'start_datetime' => $event->start_datetime->format('Y-m-d H:i:s'),
                'end_datetime' => $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null,
                'venue' => $event->venue,
                'province' => $event->province ? $event->province->name : null,
                'region' => $event->region ? $event->region->name : null,
                'community' => $event->community ? $event->community->name : null,
                'attachments' => $event->attachments->map(function($attachment) {
                    return [
                        'id' => $attachment->id,
                        'type' => $attachment->type,
                        'file_name' => $attachment->file_name,
                        'caption' => $attachment->caption,
                        'url' => url('storage/' . $attachment->file_path),
                    ];
                }),
            ],
        ]);
    }
} 