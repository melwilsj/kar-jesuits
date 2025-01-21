<?php

namespace App\Filament\Widgets;

use App\Models\JesuitFormation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormationStageOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $formationCounts = JesuitFormation::query()
            ->whereNull('end_date')
            ->selectRaw('stage_id, count(*) as count')
            ->groupBy('stage_id')
            ->with('stage')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->stage->name => $item->count]);

        return [
            Stat::make('Novices', $formationCounts['Novice 1st Year'] ?? 0 + $formationCounts['Novice 2nd Year'] ?? 0)
                ->description('In Novitiate')
                ->color('primary'),
            Stat::make('Scholastics', $formationCounts['Philosophy'] ?? 0 + $formationCounts['Theology'] ?? 0)
                ->description('In Studies')
                ->color('success'),
            Stat::make('Regents', $formationCounts['Regency'] ?? 0)
                ->description('In Regency')
                ->color('warning'),
        ];
    }
} 