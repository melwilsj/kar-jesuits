<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'event_type',
        'start_datetime',
        'end_datetime',
        'venue',
        'province_id',
        'region_id',
        'jesuit_id',
        'community_id',
        'is_public',
        'is_recurring',
        'recurrence_pattern',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_public' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EventAttachment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
} 