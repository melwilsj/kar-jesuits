<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoleAssignment extends Model
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