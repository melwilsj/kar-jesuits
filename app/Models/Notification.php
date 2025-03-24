<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
} 