<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'allowed_file_types',
        'is_private'
    ];

    protected $casts = [
        'allowed_file_types' => 'array',
        'is_private' => 'boolean'
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }
} 