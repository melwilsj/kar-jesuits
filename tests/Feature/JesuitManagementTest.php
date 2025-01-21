<?php

namespace Tests\Feature;

use App\Models\{User, Province, Community};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JesuitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_jesuit()
    {
        $this->seed();
        
        $admin = User::factory()->create()->assignRole('superadmin');
        
        $response = $this->actingAs($admin)->post('/admin/jesuits', [
            'name' => 'Test Jesuit',
            'email' => 'test@example.com',
            'type' => 'P',
            'province_id' => Province::first()->id,
            'current_community_id' => Community::first()->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_province_admin_can_manage_members()
    {
        $this->seed();
        
        $province = Province::first();
        $admin = User::factory()
            ->create(['province_id' => $province->id])
            ->assignRole('province_admin');
        
        $response = $this->actingAs($admin)->get("/admin/provinces/{$province->id}/members");
        
        $response->assertOk();
    }

    public function test_formation_stage_update()
    {
        $this->seed();
        
        $user = User::factory()->create();
        $admin = User::factory()->create()->assignRole('superadmin');
        
        $response = $this->actingAs($admin)->post("/admin/jesuits/{$user->id}/formation", [
            'stage_id' => 1,
            'start_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jesuit_formations', [
            'user_id' => $user->id,
            'stage_id' => 1
        ]);
    }
} 