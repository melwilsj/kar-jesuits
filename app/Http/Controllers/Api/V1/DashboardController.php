<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{Province, Region, Community, Institution, Group, Commission};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function getStatistics(Request $request)
    {
        $user = $request->user();
        
        return $this->successResponse([
            'counts' => $this->getCounts($user),
            'charts' => $this->getChartData($user),
            'recent_activities' => $this->getRecentActivities($user)
        ]);
    }

    private function getCounts($user)
    {
        return [
            'provinces' => Province::when(!$user->hasRole('superadmin'), function ($query) use ($user) {
                $query->whereIn('id', $user->provinces->pluck('id'));
            })->count(),
            'communities' => Community::when(!$user->hasRole('superadmin'), function ($query) use ($user) {
                $query->whereIn('id', $user->communities->pluck('id'));
            })->count(),
            // Similar counts for other models...
        ];
    }

    private function getChartData($user)
    {
        return [
            'institutions_by_type' => Institution::when(!$user->hasRole('superadmin'), function ($query) use ($user) {
                $query->whereIn('id', $user->institutions->pluck('id'));
            })
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get(),
            
            'members_by_group' => Group::when(!$user->hasRole('superadmin'), function ($query) use ($user) {
                $query->whereIn('id', $user->groups->pluck('id'));
            })
            ->withCount('members')
            ->get()
            ->pluck('members_count', 'name'),
        ];
    }

    private function getRecentActivities($user)
    {
        // Implementation for activity logging...
    }
} 