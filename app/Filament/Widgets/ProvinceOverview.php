<?php

namespace App\Filament\Widgets;

use App\Models\{User, Community, Institution};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProvinceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $province = auth()->user()->province;
        
        if (!$province) {
            return [];
        }

        return [
            Stat::make('Total Members', User::where('province_id', $province->id)->count())
                ->description('Active members in province')
                ->color('primary'),
            Stat::make('Communities', Community::where('province_id', $province->id)->count())
                ->description('Total communities')
                ->color('success'),
            Stat::make('Institutions', Institution::whereHas('community', function ($query) use ($province) {
                    $query->where('province_id', $province->id);
                })->count())
                ->description('Total institutions')
                ->color('warning'),
        ];
    }
} 