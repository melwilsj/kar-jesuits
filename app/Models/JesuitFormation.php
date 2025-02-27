<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JesuitFormation extends BaseModel
{
    protected $fillable = [
        'jesuit_id',
        'formation_stage_id',
        'current_year',
        'start_date',
        'status',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'current_year' => 'integer'
    ];

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(FormationStage::class, 'formation_stage_id');
    }
} 