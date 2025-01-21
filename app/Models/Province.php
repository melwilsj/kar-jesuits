<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'assistancy_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
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

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}