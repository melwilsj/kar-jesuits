<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ModelSnapshot;

class TimeTravelService
{
    public function getStateAt($timestamp, array $models = null)
    {
        $timestamp = Carbon::parse($timestamp);
        $result = [];
        
        $modelMap = [
            'jesuits' => \App\Models\Jesuit::class,
            'communities' => \App\Models\Community::class,
            'institutions' => \App\Models\Institution::class,
            'users' => \App\Models\User::class,
            'provinces' => \App\Models\Province::class,
            'regions' => \App\Models\Region::class
        ];

        foreach ($models ?? array_keys($modelMap) as $key) {
            $modelClass = $modelMap[$key] ?? null;
            if ($modelClass) {
                $result[$key] = $modelClass::asOf($timestamp)
                    ->get()
                    ->map(function ($model) {
                        return $this->makeReadOnly($model);
                    });
            }
        }
        
        return $result;
    }

    protected function makeReadOnly($model)
    {
        return tap($model, function ($instance) {
            $instance->makeReadOnly();
        });
    }

    public function getModelHistory($model)
    {
        return ModelSnapshot::where([
            'model_type' => get_class($model),
            'model_id' => $model->id
        ])->orderBy('snapshot_time', 'desc')->get();
    }

    public function compareStates($model, $timestamp1, $timestamp2)
    {
        $state1 = $model::asOf($timestamp1)->find($model->id);
        $state2 = $model::asOf($timestamp2)->find($model->id);
        
        return [
            'before' => $state1,
            'after' => $state2,
            'changes' => $this->getDiff($state1, $state2)
        ];
    }

    protected function getDiff($state1, $state2)
    {
        $diff = [];
        foreach ($state2->getAttributes() as $key => $value) {
            if ($state1->$key !== $value) {
                $diff[$key] = [
                    'from' => $state1->$key,
                    'to' => $value
                ];
            }
        }
        return $diff;
    }
} 