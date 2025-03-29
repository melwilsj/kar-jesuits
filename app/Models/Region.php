<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'province_id', 'assistancy_id'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function assistancy(): BelongsTo
    {
        return $this->belongsTo(Assistancy::class);
    }
} 