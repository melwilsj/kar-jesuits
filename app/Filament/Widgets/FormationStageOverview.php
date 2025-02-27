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
        $formationCounts = JesuitFormation::query()
            ->join('formation_stages', 'formation_stages.id', '=', 'jesuit_formations.formation_stage_id')
            ->whereNull('jesuit_formations.end_date')
            ->where('jesuit_formations.status', 'active')
            ->selectRaw('formation_stages.code, COUNT(*) as count')
            ->groupBy('formation_stages.code')
            ->pluck('count', 'code');

        return [
            Stat::make('Novices', 
                    $formationCounts->get('NOV1', 0) + 
                    $formationCounts->get('NOV2', 0))
                ->description('In Novitiate')
                ->color('primary'),

            Stat::make('Juniors', $formationCounts->get('JUN', 0))
                ->description('In Juniorate')
                ->color('primary'),

            Stat::make('Undergraduates', 
                    $formationCounts->get('COL1', 0) + 
                    $formationCounts->get('COL2', 0) + 
                    $formationCounts->get('COL3', 0) + 
                    $formationCounts->get('COL4', 0))
                ->description('In Undergraduate')
                ->color('primary'),
            
            Stat::make('Philosophers', 
                    $formationCounts->get('PHI1', 0) + 
                    $formationCounts->get('PHI2', 0))
                ->description('In Philosophy')
                ->color('success'),
            
            Stat::make('Regents', 
                    $formationCounts->get('REG1', 0) + 
                    $formationCounts->get('REG2', 0))
                ->description('In Regency')
                ->color('warning'),
            
            Stat::make('Theologians', 
                $formationCounts->get('THE1', 0) + 
                $formationCounts->get('THE2', 0) + 
                $formationCounts->get('THE3', 0)
            )
                ->description('In Theology')
                ->color('danger'),
        ];
    }
} 