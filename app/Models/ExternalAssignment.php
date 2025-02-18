<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExternalAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'jesuit_id',
        'assignable_type',
        'assignable_id',
        'assignment_type',
        'start_date',
        'end_date',
        'description',
        'is_active'
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

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
} 