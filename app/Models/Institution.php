<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institution extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'community_id',
        'type',
        'description',
        'contact_details',
        'student_demographics',
        'staff_demographics',
        'beneficiaries',
        'diocese',
        'taluk',
        'district',
        'state',
        'address',
        'is_active'
    ];

    protected $casts = [
        'contact_details' => 'array',
        'student_demographics' => 'array',
        'staff_demographics' => 'array',
        'beneficiaries' => 'array',
        'is_active' => 'boolean'
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'assignable');
    }

    public function externalAssignments(): MorphMany
    {
        return $this->morphMany(ExternalAssignment::class, 'assignable');
    }

    public function superiors()
    {
        return $this->morphMany(RoleAssignment::class, 'assignable')
            ->whereHas('roleType', function($query) {
                $query->where('name', 'Director')->orWhere('name', 'Principal');
            })
            ->where('is_active', true)
            ->with('jesuit');
    }

    public function diocese()
    {
        return $this->belongsTo(Diocese::class);
    }
} 