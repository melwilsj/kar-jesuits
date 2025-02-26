<?php

namespace App\Traits;

use App\Models\ModelSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

trait HasTimeTravel
{
    public static function bootHasTimeTravel()
    {
        static::updating(function ($model) {
            static::createSnapshot($model, 'update');
        });
        
        static::deleting(function ($model) {
            static::createSnapshot($model, 'delete');
        });
    }

    public static function createSnapshot($model, $action)
    {
        ModelSnapshot::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'data' => $model->getOriginal(),
            'action' => $action,
            'changed_by_id' => auth()->id(),
            'snapshot_time' => now()
        ]);
    }

    public function scopeAsOf(Builder $query, $timestamp)
    {
        $timestamp = Carbon::parse($timestamp);
        
        $query->withTrashed()
            ->where('created_at', '<=', $timestamp)
            ->whereNotExists(function ($sq) use ($timestamp) {
                $sq->from('model_snapshots')
                    ->whereColumn('model_id', $this->getTable() . '.id')
                    ->where('model_type', get_class($this))
                    ->where('snapshot_time', '<=', $timestamp)
                    ->where('action', 'delete');
            });

        foreach ($this->getTimeTraveledRelations() as $relation) {
            $query->with([$relation => function($q) use ($timestamp) {
                $q->asOf($timestamp);
            }]);
        }
        
        return $query;
    }

    protected function getTimeTraveledRelations()
    {
        return property_exists($this, 'timeTravelRelations') 
            ? $this->timeTravelRelations 
            : [];
    }
} 