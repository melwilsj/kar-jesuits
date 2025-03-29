<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\Jesuit;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        // Create random general events
        Event::factory()->count(20)->create();
        
        // Create birthday events for jesuits
        $jesuits = Jesuit::whereNotNull('dob')->get();
        $superadmin = User::where('email', 'melwilsj@jesuits.net')->first() ?? User::first();
        $count = 0;
        foreach ($jesuits as $jesuit) {
            if (!$jesuit->dob || $count > 30) continue;
            
            // Create birthday event for this year
            $birthdayThisYear = Carbon::parse($jesuit->dob)->setYear(now()->year);
            if ($birthdayThisYear->isPast()) {
                $birthdayThisYear = Carbon::parse($jesuit->dob)->setYear(now()->year + 1);
            }
            
            Event::create([
                'title' => "Birthday - {$jesuit->user->name}",
                'description' => "Birthday celebration for {$jesuit->user->name}",
                'type' => 'regular',
                'event_type' => 'birthday',
                'start_datetime' => $birthdayThisYear,
                'end_datetime' => $birthdayThisYear->copy()->addHours(24),
                'province_id' => $jesuit->province_id,
                'region_id' => $jesuit->region_id,
                'jesuit_id' => $jesuit->id,
                'community_id' => $jesuit->current_community_id,
                'is_public' => true,
                'is_recurring' => true,
                'recurrence_pattern' => 'yearly',
                'created_by' => $superadmin->id,
            ]);
            $count++;
        }
        
        // Create jubilee celebrations (for jesuits who have been priests for 25, 50 years)
        $priests = Jesuit::whereNotNull('priesthood_date')->get();
        foreach ($priests as $priest) {
            if (!$priest->priesthood_date) continue;
            
            $yearsSinceOrdination = Carbon::parse($priest->priesthood_date)->diffInYears(now());
            
            if ($yearsSinceOrdination >= 24 && $yearsSinceOrdination <= 26) {
                // Silver jubilee
                $jubileeDate = Carbon::parse($priest->priesthood_date)->addYears(25);
                if ($jubileeDate->isPast()) {
                    $jubileeDate = now()->addMonths(rand(1, 6));
                }
                
                Event::create([
                    'title' => "Silver Jubilee - {$priest->user->name}",
                    'description' => "Celebrating 25 years of priesthood for {$priest->user->name}",
                    'type' => 'special',
                    'event_type' => 'jubilee',
                    'start_datetime' => $jubileeDate,
                    'end_datetime' => $jubileeDate->copy()->addHours(8),
                    'venue' => $priest->current_community ? $priest->current_community->name : null,
                    'province_id' => $priest->province_id,
                    'region_id' => $priest->region_id,
                    'jesuit_id' => $priest->id,
                    'community_id' => $priest->current_community_id,
                    'is_public' => true,
                    'is_recurring' => false,
                    'created_by' => $superadmin->id,
                ]);
            } elseif ($yearsSinceOrdination >= 49 && $yearsSinceOrdination <= 51) {
                // Golden jubilee
                $jubileeDate = Carbon::parse($priest->priesthood_date)->addYears(50);
                if ($jubileeDate->isPast()) {
                    $jubileeDate = now()->addMonths(rand(1, 6));
                }
                
                Event::create([
                    'title' => "Golden Jubilee - {$priest->user->name}",
                    'description' => "Celebrating 50 years of priesthood for {$priest->user->name}",
                    'type' => 'special',
                    'event_type' => 'jubilee',
                    'start_datetime' => $jubileeDate,
                    'end_datetime' => $jubileeDate->copy()->addHours(8),
                    'venue' => $priest->current_community ? $priest->current_community->name : null,
                    'province_id' => $priest->province_id,
                    'region_id' => $priest->region_id,
                    'jesuit_id' => $priest->id,
                    'community_id' => $priest->current_community_id,
                    'is_public' => true,
                    'is_recurring' => false,
                    'created_by' => $superadmin->id,
                ]);
            }
        }
        
        // Create province-wide events
        $provinces = Province::all();
        foreach ($provinces as $province) {
            // Annual retreat
            $retreatDate = now()->addMonths(rand(1, 6));
            Event::create([
                'title' => "Annual Province Retreat - {$province->name}",
                'description' => "Annual retreat for all members of {$province->name}",
                'type' => 'special',
                'event_type' => 'retreat',
                'start_datetime' => $retreatDate,
                'end_datetime' => $retreatDate->copy()->addDays(8),
                'venue' => "Retreat Center, {$province->name}",
                'province_id' => $province->id,
                'is_public' => true,
                'is_recurring' => true,
                'recurrence_pattern' => 'yearly',
                'created_by' => $superadmin->id,
            ]);
            
            // Province day
            $provinceDay = now()->addMonths(rand(1, 6));
            Event::create([
                'title' => "Province Day - {$province->name}",
                'description' => "Annual gathering of all Jesuits from {$province->name}",
                'type' => 'special',
                'event_type' => 'meeting',
                'start_datetime' => $provinceDay,
                'end_datetime' => $provinceDay->copy()->addHours(12),
                'venue' => "Province House, {$province->name}",
                'province_id' => $province->id,
                'is_public' => true,
                'is_recurring' => true,
                'recurrence_pattern' => 'yearly',
                'created_by' => $superadmin->id,
            ]);
        }
    }
}