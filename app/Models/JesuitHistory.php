<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JesuitHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'jesuit_id',
        'community_id',
        'province_id',
        'category',
        'start_date',
        'end_date',
        'status',
        'remarks'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
} 