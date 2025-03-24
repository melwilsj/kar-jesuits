<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Commission extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'province_id',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'assignable');
    }

    public function activeMembers()
    {
        return $this->roleAssignments()
            ->where('is_active', true)
            ->whereNull('end_date');
    }

    public function assignHead(Jesuit $jesuit, ?string $startDate = null)
    {
        $startDate = $startDate ?? now();
        
        // End current head's role if exists
        $this->members()
            ->where('is_head', true)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'end_date' => $startDate
            ]);
        
        // Create new head assignment
        return $this->members()->create([
            'jesuit_id' => $jesuit->id,
            'is_head' => true,
            'start_date' => $startDate,
            'is_active' => true
        ]);
    }

    public function currentHead()
    {
        return $this->members()
            ->where('is_head', true)
            ->where('is_active', true)
            ->first()
            ?->jesuit;
    }

    public function head(): MorphOne
    {
        return $this->morphOne(RoleAssignment::class, 'assignable')
            ->whereHas('roleType', function($query) {
                $query->where('name', 'Commission Head');
            })
            ->where('is_active', true)
            ->with('jesuit');
    }

    public function members()
    {
        return $this->morphMany(RoleAssignment::class, 'assignable')
            ->whereHas('roleType', function($query) {
                $query->where('name', 'Commission Member');
            })
            ->where('is_active', true)
            ->with('jesuit');
    }
} 