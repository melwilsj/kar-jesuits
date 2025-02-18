<?php

namespace Tests\Feature;

use App\Models\{User, Province, ProvinceTransfer};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvinceTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_admin_can_request_transfer()
    {
        $this->seed();
        
        $fromProvince = Province::first();
        $toProvince = Province::factory()->create();
        
        $admin = User::factory()
            ->create(['province_id' => $fromProvince->id])
            ->assignRole('province_admin');
            
        $jesuit = User::factory()->create(['province_id' => $fromProvince->id]);

        $response = $this->actingAs($admin)->post("/admin/jesuits/{$jesuit->id}/transfer-request", [
            'to_province_id' => $toProvince->id,
            'notes' => 'Test transfer request'
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('province_transfers', [
            'jesuit_id' => $jesuit->jesuit->id,
            'from_province_id' => $fromProvince->id,
            'to_province_id' => $toProvince->id,
            'status' => 'pending'
        ]);
    }
} 