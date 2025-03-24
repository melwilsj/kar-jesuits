<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasResourceScoping
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user()->isSuperAdmin()) {
            return $query; // Full access
        }

        $user = auth()->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $query->where('id', null); // No access
        }

        // Province admin scope
        if ($user->isProvinceAdmin()) {
            return static::applyProvinceScope($query, $jesuit->province_id);
        }

        // Region admin scope
        if ($user->isRegionAdmin()) {
            return static::applyRegionScope($query, $jesuit->region_id);
        }

        return $query->where('id', null); // Default no access
    }

    protected static function applyProvinceScope($query, $provinceId)
    {
        return $query->where(function($q) use ($provinceId) {
            $q->where('province_id', $provinceId)
              ->orWhereHas('region', function($rq) use ($provinceId) {
                  $rq->where('province_id', $provinceId);
              });
        });
    }

    protected static function applyRegionScope($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }
} 