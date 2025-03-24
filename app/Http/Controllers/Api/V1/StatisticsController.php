<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends BaseController
{
    /**
     * Get age distribution of Jesuits.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ageDistribution(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $ageGroups = [
            '20-30' => [20, 30],
            '31-40' => [31, 40],
            '41-50' => [41, 50],
            '51-60' => [51, 60],
            '61-70' => [61, 70],
            '71-80' => [71, 80],
            '81+' => [81, 120]
        ];

        $currentDate = Carbon::now();
        $distribution = [];

        foreach ($ageGroups as $label => $range) {
            $startDate = $currentDate->copy()->subYears($range[1])->startOfDay();
            $endDate = $currentDate->copy()->subYears($range[0])->endOfDay();
            
            if ($label === '81+') {
                // For 81+, we only need to check for DOB before the start date
                $count = Jesuit::where('province_id', $jesuit->province_id)
                    ->where('is_active', true)
                    ->where('dob', '<', $startDate)
                    ->count();
            } else {
                $count = Jesuit::where('province_id', $jesuit->province_id)
                    ->where('is_active', true)
                    ->whereBetween('dob', [$startDate, $endDate])
                    ->count();
            }

            $distribution[] = [
                'age_group' => $label,
                'count' => $count
            ];
        }

        return $this->successResponse([
            'total' => Jesuit::where('province_id', $jesuit->province_id)
                ->where('is_active', true)
                ->count(),
            'distribution' => $distribution
        ]);
    }

    /**
     * Get formation stage statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function formationStats(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        // Get counts of Jesuits in each formation stage
        $stats = DB::table('jesuit_formations')
            ->join('jesuits', 'jesuit_formations.jesuit_id', '=', 'jesuits.id')
            ->where('jesuits.province_id', $jesuit->province_id)
            ->where('jesuits.is_active', true)
            ->whereNull('jesuit_formations.end_date')
            ->select('jesuit_formations.stage', DB::raw('count(*) as count'))
            ->groupBy('jesuit_formations.stage')
            ->orderBy('jesuit_formations.stage')
            ->get();

        return $this->successResponse([
            'total' => Jesuit::where('province_id', $jesuit->province_id)
                ->where('is_active', true)
                ->count(),
            'formation_stats' => $stats
        ]);
    }

    /**
     * Get geographical distribution statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function geographicalDistribution(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        // Get diocese-wise distribution
        $stats = DB::table('jesuits')
            ->join('communities', 'jesuits.current_community_id', '=', 'communities.id')
            ->join('dioceses', 'communities.diocese_id', '=', 'dioceses.id')
            ->where('jesuits.province_id', $jesuit->province_id)
            ->where('jesuits.is_active', true)
            ->select('dioceses.name as diocese', DB::raw('count(*) as count'))
            ->groupBy('dioceses.name')
            ->orderBy('count', 'desc')
            ->get();

        return $this->successResponse([
            'total' => Jesuit::where('province_id', $jesuit->province_id)
                ->where('is_active', true)
                ->count(),
            'diocese_distribution' => $stats
        ]);
    }

    /**
     * Get ministry type distribution statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ministryDistribution(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        // Get distribution by ministry types
        $stats = DB::table('jesuits')
            ->join('role_assignments', 'jesuits.id', '=', 'role_assignments.jesuit_id')
            ->join('role_types', 'role_assignments.role_type_id', '=', 'role_types.id')
            ->where('jesuits.province_id', $jesuit->province_id)
            ->where('jesuits.is_active', true)
            ->where('role_assignments.is_active', true)
            ->whereNull('role_assignments.end_date')
            ->select('role_types.ministry_area', DB::raw('count(DISTINCT jesuits.id) as count'))
            ->groupBy('role_types.ministry_area')
            ->orderBy('count', 'desc')
            ->get();

        return $this->successResponse([
            'total' => Jesuit::where('province_id', $jesuit->province_id)
                ->where('is_active', true)
                ->count(),
            'ministry_distribution' => $stats
        ]);
    }

    /**
     * Get yearly trends statistics.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearlyTrends(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        // For now, return placeholder data
        // The actual implementation would involve complex queries analyzing changes over time
        return $this->successResponse([
            'message' => 'Yearly trends feature will be implemented in a future update',
            'placeholder_data' => [
                'years' => ['2018', '2019', '2020', '2021', '2022', '2023'],
                'new_vocations' => [12, 10, 8, 11, 9, 7],
                'ordinations' => [8, 9, 7, 6, 8, 5],
                'deaths' => [4, 3, 5, 6, 4, 3],
                'active_members' => [220, 228, 231, 236, 241, 245]
            ]
        ]);
    }
} 