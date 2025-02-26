<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelSnapshot extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'data',
        'action',
        'changed_by_id',
        'snapshot_time'
    ];

    protected $casts = [
        'data' => 'array',
        'snapshot_time' => 'datetime'
    ];

    public function snapshotable()
    {
        return $this->morphTo('model');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
} 