<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EventAnalytics extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Event::query();
        
        // Scope to province if needed
        if ($user->isProvinceAdmin()) {
            $query->where('province_id', $user->jesuit->province_id);
        }
        
        return [
            Stat::make('Upcoming Events', $query->clone()->where('start_datetime', '>', now())->count())
                ->description('Events in the next 30 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('Past Events', $query->clone()->where('end_datetime', '<', now())->count())
                ->description('Events in the last 30 days')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),
        ];
    }
} 