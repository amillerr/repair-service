<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatcherRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_filter_requests_by_status(): void
    {
        $dispatcher = User::factory()->dispatcher()->create();
        Request::factory()->count(2)->create(['status' => Request::STATUS_NEW]);
        Request::factory()->count(3)->create(['status' => Request::STATUS_DONE]);

        $response = $this->actingAs($dispatcher, 'sanctum')
            ->getJson('/api/dispatcher/requests?status=' . Request::STATUS_NEW);

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_dispatcher_can_assign_master_to_request(): void
    {
        $dispatcher = User::factory()->dispatcher()->create();
        $master = User::factory()->master()->create();
        $request = Request::factory()->create(['status' => Request::STATUS_NEW]);

        $response = $this->actingAs($dispatcher, 'sanctum')
            ->postJson("/api/dispatcher/requests/{$request->id}/assign", [
                'master_id' => $master->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $request->id)
            ->assertJsonPath('data.status', Request::STATUS_ASSIGNED)
            ->assertJsonPath('data.assigned_to', $master->id);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_ASSIGNED,
            'assigned_to' => $master->id,
        ]);
    }

    public function test_cannot_assign_canceled_or_done_request(): void
    {
        $dispatcher = User::factory()->dispatcher()->create();
        $master = User::factory()->master()->create();

        $canceled = Request::factory()->create(['status' => Request::STATUS_CANCELED]);
        $done = Request::factory()->create(['status' => Request::STATUS_DONE]);

        $this->actingAs($dispatcher, 'sanctum')
            ->postJson("/api/dispatcher/requests/{$canceled->id}/assign", [
                'master_id' => $master->id,
            ])
            ->assertStatus(409);

        $this->actingAs($dispatcher, 'sanctum')
            ->postJson("/api/dispatcher/requests/{$done->id}/assign", [
                'master_id' => $master->id,
            ])
            ->assertStatus(409);
    }

    public function test_dispatcher_can_cancel_request(): void
    {
        $dispatcher = User::factory()->dispatcher()->create();
        $request = Request::factory()->create(['status' => Request::STATUS_NEW]);

        $response = $this->actingAs($dispatcher, 'sanctum')
            ->postJson("/api/dispatcher/requests/{$request->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.id', $request->id)
            ->assertJsonPath('data.status', Request::STATUS_CANCELED);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_CANCELED,
        ]);
    }
}

