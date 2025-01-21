<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvinceTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'from_province_id',
        'to_province_id',
        'status',
        'request_date',
        'completion_date',
        'notes'
    ];

    protected $casts = [
        'request_date' => 'date',
        'completion_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'from_province_id');
    }

    public function toProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'to_province_id');
    }
} 