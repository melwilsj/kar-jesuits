<?php

namespace App\Filament\Widgets;

use App\Models\JesuitFormation;
use App\Models\FormationStage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FormationStageOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return $this->getAllStats();
        }

        if ($user->isProvinceAdmin()) {
            return [
                ...$this->getProvinceStats($user->jesuit->province_id),
                ...$this->getRegionStats($user->jesuit->province_id)
            ];
        }

        if ($user->isRegionAdmin()) {
            return $this->getRegionStats($user->jesuit->region_id);
        }

        return [];
    }

    protected function getAllStats()
    {
        $formationCounts = JesuitFormation::query()
            ->join('formation_stages', 'formation_stages.id', '=', 'jesuit_formations.formation_stage_id')
            ->whereNull('jesuit_formations.end_date')
            ->where('jesuit_formations.status', 'active')
            ->selectRaw('formation_stages.code, COUNT(*) as count')
            ->groupBy('formation_stages.code')
            ->pluck('count', 'code');

        return $this->formatStats($formationCounts);
    }

    protected function getProvinceStats($provinceId)
    {
        $formationCounts = JesuitFormation::query()
            ->join('formation_stages', 'formation_stages.id', '=', 'jesuit_formations.formation_stage_id')
            ->join('jesuits', 'jesuits.id', '=', 'jesuit_formations.jesuit_id')
            ->where('jesuits.province_id', $provinceId)
            ->whereNull('region_id')  // Only province members
            ->whereNull('jesuit_formations.end_date')
            ->where('jesuit_formations.status', 'active')
            ->selectRaw('formation_stages.code, COUNT(*) as count')
            ->groupBy('formation_stages.code')
            ->pluck('count', 'code');

        return $this->formatStats($formationCounts, 'Province');
    }

    protected function getRegionStats($provinceId)
    {
        $formationCounts = JesuitFormation::query()
            ->join('formation_stages', 'formation_stages.id', '=', 'jesuit_formations.formation_stage_id')
            ->join('jesuits', 'jesuits.id', '=', 'jesuit_formations.jesuit_id')
            ->whereNotNull('jesuits.region_id')
            ->where('jesuits.province_id', $provinceId)
            ->whereNull('jesuit_formations.end_date')
            ->where('jesuit_formations.status', 'active')
            ->selectRaw('formation_stages.code, COUNT(*) as count')
            ->groupBy('formation_stages.code')
            ->pluck('count', 'code');

        return $this->formatStats($formationCounts, 'Region');
    }

    protected function formatStats($formationCounts, $prefix = '')
    {
        $prefix = $prefix ? "$prefix " : '';
        
        return [
            Stat::make($prefix . 'Novices', 
                    $formationCounts->get('NOV1', 0) + 
                    $formationCounts->get('NOV2', 0))
                ->description("In {$prefix}Novitiate")
                ->color('primary'),

            Stat::make($prefix . 'Juniors', $formationCounts->get('JUN', 0))
                ->description("In {$prefix}Juniorate")
                ->color('primary'),

            Stat::make($prefix . 'Undergraduates', 
                    $formationCounts->get('COL1', 0) + 
                    $formationCounts->get('COL2', 0) + 
                    $formationCounts->get('COL3', 0) + 
                    $formationCounts->get('COL4', 0))
                ->description("In {$prefix}Undergraduate")
                ->color('primary'),
            
            Stat::make($prefix . 'Philosophers', 
                    $formationCounts->get('PHI1', 0) + 
                    $formationCounts->get('PHI2', 0))
                ->description("In {$prefix}Philosophy")
                ->color('success'),
            
            Stat::make($prefix . 'Regents', 
                    $formationCounts->get('REG1', 0) + 
                    $formationCounts->get('REG2', 0))
                ->description("In {$prefix}Regency")
                ->color('warning'),
            
            Stat::make($prefix . 'Theologians', 
                    $formationCounts->get('THE1', 0) + 
                    $formationCounts->get('THE2', 0) + 
                    $formationCounts->get('THE3', 0))
                ->description("In {$prefix}Theology")
                ->color('danger'),
        ];
    }
} 