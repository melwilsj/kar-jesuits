<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Jesuit, Community};
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimeTravelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_state_at_specific_time()
    {
        $this->seed();
        $admin = User::factory()->create()->assignRole('superadmin');
        
        $jesuit = Jesuit::first();
        $originalName = $jesuit->user->name;
        
        // Make a change
        $jesuit->user->update(['name' => 'Updated Name']);
        
        $response = $this->actingAs($admin)->post('/api/time-travel/state', [
            'timestamp' => now()->subMinute()->format('Y-m-d H:i:s'),
            'models' => ['jesuits']
        ]);

        $response->assertOk();
        $this->assertEquals($originalName, $response->json('data.jesuits.0.user.name'));
    }

    public function test_can_get_model_history()
    {
        $this->seed();
        $admin = User::factory()->create()->assignRole('superadmin');
        
        $jesuit = Jesuit::first();
        $jesuit->update(['status' => 'Updated Status']);
        
        $response = $this->actingAs($admin)->get("/api/time-travel/history/Jesuit/{$jesuit->id}");
        
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
} 