<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'community_id',
        'type',
        'description',
        'contact_details',
        'student_demographics',
        'staff_demographics',
        'diocese',
        'taluk',
        'district',
        'state',
        'is_active'
    ];

    protected $casts = [
        'contact_details' => 'array',
        'student_demographics' => 'array',
        'staff_demographics' => 'array',
        'is_active' => 'boolean'
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
} 