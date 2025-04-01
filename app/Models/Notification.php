<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'event_id',
        'scheduled_for',
        'sent_at',
        'is_sent',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
        'metadata' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    /**
     * The users who have read this notification
     */
    public function readBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_reads')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Check if the given user has read this notification
     */
    public function isReadBy(User $user): bool
    {
        return $this->readBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Get all unique users who should receive this notification.
     *
     * @return Collection<User>
     */
    public function getRecipientUsers(): Collection
    {
        $userIds = collect();
        $recipientTypes = $this->recipients->pluck('recipient_type')->unique();
        $recipientIdsByType = $this->recipients->groupBy('recipient_type');

        if ($recipientTypes->contains('all')) {
            // If 'all' is present, return all active users immediately.
            // Consider performance for very large user bases.
            // Ensure users have FCM tokens if that's a requirement for receiving.
            return User::where('is_active', true)
                       // Optionally filter users who have FCM tokens:
                       // ->whereNotNull('fcm_tokens')
                       // ->whereJsonLength('fcm_tokens', '>', 0)
                       ->get();
        }

        // Direct Users
        if ($recipientTypes->contains('user')) {
            $userIds = $userIds->merge($recipientIdsByType->get('user', collect())->pluck('recipient_id'));
        }

        // Province Users
        if ($recipientTypes->contains('province')) {
            $provinceIds = $recipientIdsByType->get('province', collect())->pluck('recipient_id');
            if ($provinceIds->isNotEmpty()) {
                $userIds = $userIds->merge(
                    User::whereHas('jesuit', fn(Builder $query) => $query->whereIn('province_id', $provinceIds))
                        ->pluck('id')
                );
            }
        }

        // Region Users
        if ($recipientTypes->contains('region')) {
            $regionIds = $recipientIdsByType->get('region', collect())->pluck('recipient_id');
            if ($regionIds->isNotEmpty()) {
                $userIds = $userIds->merge(
                    User::whereHas('jesuit', fn(Builder $query) => $query->whereIn('region_id', $regionIds))
                        ->pluck('id')
                );
            }
        }

        // Community Users
        if ($recipientTypes->contains('community')) {
            $communityIds = $recipientIdsByType->get('community', collect())->pluck('recipient_id');
            if ($communityIds->isNotEmpty()) {
                $userIds = $userIds->merge(
                    User::whereHas('jesuit', fn(Builder $query) => $query->whereIn('current_community_id', $communityIds))
                        ->pluck('id')
                );
            }
        }

        // Fetch unique, active users based on collected IDs.
        $uniqueUserIds = $userIds->unique()->filter(); // Filter out null/empty values

        if ($uniqueUserIds->isEmpty()) {
            return collect(); // Return empty collection if no specific users found
        }

        return User::whereIn('id', $uniqueUserIds)
                   ->where('is_active', true)
                   // Optionally filter users who have FCM tokens:
                   // ->whereNotNull('fcm_tokens')
                   // ->whereJsonLength('fcm_tokens', '>', 0)
                   ->get();
    }
} 