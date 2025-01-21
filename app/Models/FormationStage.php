<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormationStage extends Model
{
    protected $fillable = [
        'name',
        'order',
        'has_years',
        'max_years'
    ];

    protected $casts = [
        'has_years' => 'boolean',
        'order' => 'integer',
        'max_years' => 'integer'
    ];

    public function formations(): HasMany
    {
        return $this->hasMany(JesuitFormation::class, 'stage_id');
    }
} 