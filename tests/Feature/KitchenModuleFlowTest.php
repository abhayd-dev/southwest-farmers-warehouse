<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KitchenModuleFlowTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure there is at least one admin user to act as
        $this->user = \App\Models\WareUser::firstOrCreate(
            ['email' => 'admin@kitchen.test'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'super_admin']
        );
    }

    public function test_kds_screen_loads()
    {
        $response = $this->actingAs($this->user)->get('/kitchen/kds');
        $response->assertStatus(200);
    }

    public function test_cookbook_loads()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->user)->get('/kitchen/cookbook');
        $response->assertStatus(200);
    }

    public function test_production_logs_loads()
    {
        $response = $this->actingAs($this->user)->get('/kitchen/production');
        $response->assertStatus(200);
    }

    public function test_sales_ranking_loads()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->user)->get('/kitchen/reports/sales-ranking');
        $response->assertStatus(200);
    }

    public function test_availability_matrix_loads()
    {
        $response = $this->actingAs($this->user)->get('/kitchen/availability');
        $response->assertStatus(200);
    }

    public function test_staff_timesheets_loads()
    {
        $response = $this->actingAs($this->user)->get('/kitchen/staff-timesheets');
        $response->assertStatus(200);
    }
}
