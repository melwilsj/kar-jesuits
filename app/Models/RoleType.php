<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleType extends BaseModel
{
    protected $fillable = [
        'name',
        'category',
        'description'
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }
} 