<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JesuitProfile extends Model
{
    protected $fillable = [
        'user_id',
        'dob',
        'joining_date',
        'priesthood_date',
        'final_vows_date',
        'academic_qualifications',
        'publications'
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'priesthood_date' => 'date',
        'final_vows_date' => 'date',
        'academic_qualifications' => 'array',
        'publications' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 