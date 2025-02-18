<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommonHouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'assistancy_id',
        'address',
        'contact_details',
        'is_active'
    ];

    protected $casts = [
        'contact_details' => 'array',
        'is_active' => 'boolean'
    ];

    public function assistancy(): BelongsTo
    {
        return $this->belongsTo(Assistancy::class);
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ExternalAssignment::class, 'assignable');
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'assignable');
    }
} 