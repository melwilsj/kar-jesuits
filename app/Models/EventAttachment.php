<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'caption',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
} 