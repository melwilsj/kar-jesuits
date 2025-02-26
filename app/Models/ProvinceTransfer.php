<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvinceTransfer extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'jesuit_id',
        'from_province_id',
        'to_province_id',
        'requested_by',
        'status',
        'request_date',
        'effective_date',
        'processed_date',
        'processed_by',
        'notes',
        'rejection_reason'
    ];

    protected $casts = [
        'request_date' => 'date',
        'effective_date' => 'date',
        'processed_date' => 'date'
    ];

    public function jesuit(): BelongsTo
    {
        return $this->belongsTo(Jesuit::class);
    }

    public function fromProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'from_province_id');
    }

    public function toProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'to_province_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
} 