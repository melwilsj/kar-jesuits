<?php

namespace App\Models;

use App\Constants\RoleTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Jesuit extends BaseModel
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

    protected $timeTravelRelations = [
        'user',
        'province',
        'currentCommunity',
        'roleAssignments'
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
            ->whereIn('role_type_id', RoleType::whereIn('name', RoleTypes::SUPERIOR_ROLES)->pluck('id'))
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

    public function canBeAssignedTo(Community $community): bool
    {
        if ($community->isCommonHouse()) {
            // Any Jesuit can be assigned to common houses
            return true;
        }

        // For regular communities, Jesuit must be from same province
        return $this->province_id === $community->province_id;
    }

    public function assignToCommunity(Community $community, string $status = 'Member', ?string $startDate = null): void
    {
        $startDate = $startDate ?? now();

        // End current community assignment if exists
        if ($this->current_community_id) {
            JesuitHistory::create([
                'jesuit_id' => $this->id,
                'community_id' => $this->current_community_id,
                'province_id' => $this->currentCommunity->isCommonHouse() ? null : $this->province_id,
                'assistancy_id' => $this->currentCommunity->isCommonHouse() ? $this->currentCommunity->assistancy_id : null,
                'category' => $this->category,
                'start_date' => $this->histories()->latest()->first()?->start_date ?? $startDate,
                'end_date' => $startDate,
                'status' => $status,
                'remarks' => 'Transfer out'
            ]);
        }

        // Create new assignment
        $this->update(['current_community_id' => $community->id]);

        JesuitHistory::create([
            'jesuit_id' => $this->id,
            'community_id' => $community->id,
            'province_id' => $community->isCommonHouse() ? null : $this->province_id,
            'assistancy_id' => $community->isCommonHouse() ? $community->assistancy_id : null,
            'category' => $this->category,
            'start_date' => $startDate,
            'status' => $status,
            'remarks' => 'New assignment'
        ]);
    }
} 