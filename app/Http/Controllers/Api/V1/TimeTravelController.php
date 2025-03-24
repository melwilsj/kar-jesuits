<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\TimeTravelService;
use Illuminate\Http\Request;
use App\Models\User;

class TimeTravelController extends BaseController
{
    protected $timeTravelService;

    public function __construct(TimeTravelService $timeTravelService)
    {
        $this->timeTravelService = $timeTravelService;
    }

    public function getStateAt(Request $request)
    {
        $this->authorize('viewTimeTravel', User::class);
        
        $validated = $request->validate([
            'timestamp' => 'required|date',
            'models' => 'required|array',
            'models.*' => 'required|string|in:jesuits,communities,institutions,users,provinces,regions'
        ]);

        $state = $this->timeTravelService->getStateAt(
            $validated['timestamp'],
            $validated['models']
        );

        return $this->successResponse($state);
    }

    public function getModelHistory(Request $request)
    {
        $this->authorize('viewTimeTravel', User::class);
        
        $validated = $request->validate([
            'model_type' => 'required|string|in:Jesuit,Community,Institution',
            'model_id' => 'required|integer'
        ]);

        $modelClass = "App\\Models\\" . $validated['model_type'];
        $model = $modelClass::findOrFail($validated['model_id']);

        $history = $this->timeTravelService->getModelHistory($model);
        
        return $this->successResponse($history);
    }
} 