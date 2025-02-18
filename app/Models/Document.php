<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'jesuit_id',
        'category_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'is_private',
        'metadata'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_private' => 'boolean',
        'metadata' => 'array'
    ];

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function allowedRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'document_access');
    }
} 