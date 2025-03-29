<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Province extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'assistancy_id',
        'country',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function assistancy(): BelongsTo
    {
        return $this->belongsTo(Assistancy::class);
    }

    public function jesuits(): HasMany
    {
        return $this->hasMany(Jesuit::class);
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'assignable');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(ProvinceTransfer::class, 'to_province_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(ProvinceTransfer::class, 'from_province_id');
    }

    public function activeJesuits()
    {
        return $this->jesuits()->where('is_active', true);
    }

    public function members()
    {
        return $this->jesuits();
    }
}