<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormationSeeder extends Seeder
{
    public function run(): void
    {
        // Get all scholastics
        $jesuits = DB::table('jesuits')->whereIn('category', ['S', 'NS'])->get();
        
        // Get formation stages ordered by their sequence
        $allStages = DB::table('formation_stages')->orderBy('order')->get();
        
        // Group stages for different categories
        $noviceStages = $allStages->where('order', '<=', 2); // Pre-Novitiate and Novitiate
        $otherStages = $allStages->where('order', '>', 2); // All other stages
        
        foreach ($jesuits as $jesuit) {
            // Clear any existing formation records for this jesuit
            DB::table('jesuit_formations')->where('jesuit_id', $jesuit->id)->delete();
            
            // Determine which stages to use based on category
            $availableStages = $jesuit->category === 'NS' ? $noviceStages : $otherStages;
            
            // Randomly select one stage
            $selectedStage = $availableStages->random();
            
            // Calculate a random start date within the last 2 years
            $startDate = Carbon::now()->subMonths(rand(1, 24));
            
            // Insert the formation record
            DB::table('jesuit_formations')->insert([
                'jesuit_id' => $jesuit->id,
                'formation_stage_id' => $selectedStage->id,
                'start_date' => $startDate,
                'end_date' => rand(0, 1) ? $startDate->copy()->addYears(1) : null,
                'current_year' => rand(1, 2),
                'status' => 'active',
                'notes' => "Sample formation record for {$selectedStage->name}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 