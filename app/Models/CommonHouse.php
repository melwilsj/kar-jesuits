<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommonHouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'assistancy_id',
        'address',
        'contact_details'
    ];

    protected $casts = [
        'contact_details' => 'array'
    ];

    public function assistancy(): BelongsTo
    {
        return $this->belongsTo(Assistancy::class);
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ExternalAssignment::class, 'assignable');
    }
} 