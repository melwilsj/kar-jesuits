<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoleAssignment extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'jesuit_id',
        'role_type_id',
        'assignable_type',
        'assignable_id',
        'start_date',
        'end_date',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    protected static function booted()
    {
        static::created(function ($assignment) {
            if ($assignment->assignable_type === Community::class) {
                $assignment->assignable->createSnapshot($assignment->assignable, 'update');
            }
        });

        static::updated(function ($assignment) {
            if ($assignment->assignable_type === Community::class) {
                $assignment->assignable->createSnapshot($assignment->assignable, 'update');
            }
        });
    }

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function roleType(): BelongsTo
    {
        return $this->belongsTo(RoleType::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
} 