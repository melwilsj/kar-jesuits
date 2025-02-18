<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Community extends Model
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
        'is_active'
    ];

    protected $casts = [
        'is_formation_house' => 'boolean',
        'is_attached_house' => 'boolean',
        'is_active' => 'boolean'
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
        return $this->roleAssignments()
            ->where('is_active', true)
            ->whereNull('end_date')
            ->whereHas('roleType', function($query) {
                $query->whereIn('name', ['Superior', 'Rector', 'Coordinator']);
            })
            ->first()
            ->jesuit
            ->user;
    }

    public function superiorHistory()
    {
        return $this->roleAssignments()
            ->whereHas('roleType', function($query) {
                $query->whereIn('name', ['Superior', 'Rector', 'Coordinator']);
            })
            ->orderBy('start_date', 'desc');
    }

    public function assignSuperior(Jesuit $jesuit, string $roleType = 'Superior', ?string $startDate = null)
    {
        $startDate = $startDate ?? now();
        
        // Get or create role type
        $roleTypeModel = RoleType::firstOrCreate(
            ['name' => $roleType],
            [
                'description' => "Head of Community ({$roleType})",
                'category' => 'community'
            ]
        );
        
        // End current superior's role if exists
        $currentAssignment = $this->roleAssignments()
            ->where('is_active', true)
            ->whereNull('end_date')
            ->whereHas('roleType', function($query) use ($roleType) {
                $query->where('name', $roleType);
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
                'province_id' => $this->province_id,
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
            'province_id' => $this->province_id,
            'category' => $jesuit->category,
            'start_date' => $startDate,
            'status' => 'Superior',
            'remarks' => "Appointed as {$roleType}"
        ]);

        return $assignment;
    }
} 