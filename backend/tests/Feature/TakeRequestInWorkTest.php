<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TakeRequestInWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_take_new_request_in_work(): void
    {
        $master = User::factory()->master()->create();
        $request = Request::factory()->create(['status' => Request::STATUS_NEW]);

        $response = $this->actingAs($master, 'sanctum')
            ->postJson("/api/requests/{$request->id}/take");

        $response->assertOk()
            ->assertJson([
                'id' => $request->id,
                'status' => Request::STATUS_IN_PROGRESS,
                'assigned_to' => $master->id,
            ]);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_IN_PROGRESS,
            'assigned_to' => $master->id,
        ]);
    }

    public function test_second_take_in_work_returns_conflict(): void
    {
        $master1 = User::factory()->master()->create();
        $master2 = User::factory()->master()->create();
        $request = Request::factory()->create(['status' => Request::STATUS_NEW]);

        $this->actingAs($master1, 'sanctum')
            ->postJson("/api/requests/{$request->id}/take")
            ->assertOk();

        $response = $this->actingAs($master2, 'sanctum')
            ->postJson("/api/requests/{$request->id}/take");

        $response->assertStatus(409);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_IN_PROGRESS,
            'assigned_to' => $master1->id,
        ]);
    }
}

