<?php

namespace App\Models;

use App\Constants\RoleTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Community extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'province_id',
        'region_id',
        'parent_community_id',
        'superior_type',
        'address',
        'diocese',
        'taluk',
        'district',
        'state',
        'phone',
        'email',
        'is_formation_house',
        'is_attached_house',
        'is_active',
        'assistancy_id',
        'is_common_house'
    ];

    protected $casts = [
        'is_formation_house' => 'boolean',
        'is_attached_house' => 'boolean',
        'is_active' => 'boolean',
        'is_common_house' => 'boolean'
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function parentCommunity(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'parent_community_id');
    }

    public function attachedHouses(): HasMany
    {
        return $this->hasMany(Community::class, 'parent_community_id');
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
    }

    public function jesuits(): HasMany
    {
        return $this->hasMany(Jesuit::class, 'current_community_id');
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'assignable');
    }

    public function formationHistories(): HasMany
    {
        return $this->hasMany(FormationHistory::class);
    }

    public function superior()
    {
        $currentAssignment = $this->roleAssignments()
            ->where('is_active', true)
            ->whereHas('roleType', function($query) {
                $query->whereIn('name', RoleTypes::SUPERIOR_ROLES);
            })
            ->first();

        return $currentAssignment?->jesuit;
    }

    public function superiorHistory()
    {
        return $this->roleAssignments()
            ->whereHas('roleType', function($query) {
                $query->whereIn('name', RoleTypes::SUPERIOR_ROLES);
            })
            ->orderBy('start_date', 'desc');
    }

    public function assignSuperior(Jesuit $jesuit, string $roleType, $startDate = null)
    {
        // Validate superior assignment
        if ($this->isCommonHouse() && !auth()->user()?->isPOSA()) {
            throw new \Exception('Only POSA can assign superiors to common houses');
        }

        if (!$this->isCommonHouse() && $jesuit->province_id !== $this->province_id) {
            throw new \Exception('Superior must be from the same province as the community');
        }

        if (!in_array($roleType, RoleTypes::SUPERIOR_ROLES)) {
            throw new \Exception('Invalid superior type');
        }

        // Check if the role type matches the community type
        if ($this->is_attached_house && $roleType !== 'Coordinator') {
            throw new \Exception('Attached houses can only have Coordinators');
        }
        if (!$this->is_attached_house && $roleType === 'Coordinator') {
            throw new \Exception('Only attached houses can have Coordinators');
        }

        // Check if the Jesuit already has an active superior role elsewhere
        $existingRole = $jesuit->roleAssignments()
            ->where('is_active', true)
            ->whereHas('roleType', function($query) {
                $query->whereIn('name', RoleTypes::SUPERIOR_ROLES);
            })
            ->first();

        if ($existingRole) {
            throw new \Exception('This Jesuit is already a superior/coordinator in another community');
        }

        $startDate = $startDate ?? now();
        $roleTypeModel = RoleType::where('name', $roleType)->firstOrFail();

        // Get current active superior assignment
        $currentAssignment = $this->roleAssignments()
            ->where('is_active', true)
            ->whereHas('roleType', function($query) use ($roleType) {
                $query->whereIn('name', RoleTypes::SUPERIOR_ROLES);
            })
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update([
                'is_active' => false,
                'end_date' => $startDate
            ]);

            // Create history entry for previous superior
            JesuitHistory::create([
                'jesuit_id' => $currentAssignment->jesuit_id,
                'community_id' => $this->id,
                'province_id' => $this->isCommonHouse() ? null : $this->province_id,
                'assistancy_id' => $this->isCommonHouse() ? $this->assistancy_id : null,
                'category' => $currentAssignment->jesuit->category,
                'start_date' => $currentAssignment->start_date,
                'end_date' => $startDate,
                'status' => 'Superior',
                'remarks' => "Served as {$roleType}"
            ]);
        }

        // Create new role assignment
        $assignment = $this->roleAssignments()->create([
            'jesuit_id' => $jesuit->id,
            'role_type_id' => $roleTypeModel->id,
            'start_date' => $startDate,
            'is_active' => true
        ]);

        // Create history entry for new superior
        JesuitHistory::create([
            'jesuit_id' => $jesuit->id,
            'community_id' => $this->id,
            'province_id' => $this->isCommonHouse() ? null : $this->province_id,
            'assistancy_id' => $this->isCommonHouse() ? $this->assistancy_id : null,
            'category' => $jesuit->category,
            'start_date' => $startDate,
            'status' => 'Superior',
            'remarks' => "Appointed as {$roleType}"
        ]);

        return $assignment;
    }

    public function assistancy(): BelongsTo
    {
        return $this->belongsTo(Assistancy::class);
    }

    public function isCommonHouse(): bool
    {
        return $this->is_common_house && $this->assistancy_id !== null;
    }

    public function getAdministrativeHeadAttribute()
    {
        return $this->isCommonHouse() 
            ? $this->assistancy->provincial 
            : $this->province->provincial;
    }

    public function scopeCommonHouses($query)
    {
        return $query->where('is_common_house', true);
    }

    public function scopeRegularHouses($query)
    {
        return $query->where('is_common_house', false);
    }

    public function scopeInProvince($query, $provinceId)
    {
        return $query->where('province_id', $provinceId)->regularHouses();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLeaderAttribute()
    {
        if ($this->is_attached_house) {
            return $this->roleAssignments()
                ->where('is_active', true)
                ->whereHas('roleType', function($query) {
                    $query->where('name', 'Coordinator');
                })
                ->first()?->jesuit;
        }
        
        return $this->superior();
    }

    public function assignLeader(Jesuit $jesuit, ?string $startDate = null): void
    {
        $roleType = $this->is_attached_house ? 'Coordinator' : 'Superior';
        
        if ($this->is_attached_house && !$this->parentCommunity) {
            throw new \Exception('Attached house must have a parent community');
        }
        
        $this->assignSuperior($jesuit, $roleType, $startDate);
    }
} 