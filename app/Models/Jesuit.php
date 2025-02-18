<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jesuit extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'code',
        'category',
        'prefix_modifier',
        'photo_url',
        'dob',
        'joining_date',
        'priesthood_date',
        'final_vows_date',
        'dod',
        'is_active',
        'status',
        'academic_qualifications',
        'publications',
        'languages',
        'is_external',
        'notes'
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'priesthood_date' => 'date',
        'final_vows_date' => 'date',
        'dod' => 'date',
        'is_active' => 'boolean',
        'is_external' => 'boolean',
        'academic_qualifications' => 'array',
        'publications' => 'array',
        'languages' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function currentCommunity(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'current_community_id');
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function activeRoles()
    {
        return $this->roleAssignments()
            ->where('is_active', true)
            ->whereNull('end_date');
    }

    public function externalAssignments(): HasMany
    {
        return $this->hasMany(ExternalAssignment::class);
    }

    public function provinceTransfers(): HasMany
    {
        return $this->hasMany(ProvinceTransfer::class);
    }

    public function isProvincial(): bool
    {
        return $this->activeRoles()
            ->whereHasMorph('assignable', [Province::class])
            ->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)
            ->exists();
    }

    public function isSuperior(): bool
    {
        return $this->activeRoles()
            ->whereHasMorph('assignable', [Community::class])
            ->whereIn('role_type_id', RoleType::whereIn('name', ['Superior', 'Rector'])->pluck('id'))
            ->exists();
    }

    public function isInFormation(): bool
    {
        return $this->formationHistory()
            ->where('is_active', true)
            ->exists();
    }

    public function getDisplayNameAttribute(): string
    {
        $prefix = $this->category;
        if ($this->prefix_modifier) {
            $prefix = $this->prefix_modifier . $this->category;
        }
        return "{$this->user->name} ({$prefix})";
    }

    public function histories(): HasMany
    {
        return $this->hasMany(JesuitHistory::class);
    }
} 