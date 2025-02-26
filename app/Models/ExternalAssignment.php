<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExternalAssignment extends BaseModel
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

    public function scopeInCommonHouses($query)
    {
        return $query->where('assignable_type', Community::class)
            ->whereHas('assignable', function($q) {
                $q->where('is_common_house', true);
            });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInProvince($query, $provinceId)
    {
        return $query->where('assignable_type', Community::class)
            ->whereHas('assignable', function($q) use ($provinceId) {
                $q->where('province_id', $provinceId)
                    ->where('is_common_house', false);
            });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if ($assignment->assignable_type === Community::class) {
                $community = Community::findOrFail($assignment->assignable_id);
                $this->authorize('create', [self::class, $community]);
            }
        });
    }
} 