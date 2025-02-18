<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormationStage extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'order'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    public function jesuitHistories(): HasMany
    {
        return $this->hasMany(JesuitHistory::class, 'formation_stage_id');
    }

    public function activeJesuits()
    {
        return $this->jesuitHistories()
            ->where('is_active', true)
            ->whereNull('end_date');
    }

    public function hasYears(): bool
    {
        return in_array($this->code, ['COL', 'PHI', 'REG', 'PG', 'THE']);
    }
} 