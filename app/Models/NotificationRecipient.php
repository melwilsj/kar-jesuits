<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'recipient_type',
        'recipient_id',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function recipient()
    {
        if ($this->recipient_type === 'user') {
            return $this->belongsTo(User::class, 'recipient_id');
        } elseif ($this->recipient_type === 'province') {
            return $this->belongsTo(Province::class, 'recipient_id');
        } elseif ($this->recipient_type === 'region') {
            return $this->belongsTo(Region::class, 'recipient_id');
        } elseif ($this->recipient_type === 'community') {
            return $this->belongsTo(Community::class, 'recipient_id');
        }
        
        return null;
    }
} 