<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_sees_only_own_requests(): void
    {
        $master1 = User::factory()->master()->create();
        $master2 = User::factory()->master()->create();

        Request::factory()->count(2)->create([
            'assigned_to' => $master1->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        Request::factory()->count(3)->create([
            'assigned_to' => $master2->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        $response = $this->actingAs($master1, 'sanctum')
            ->getJson('/api/master/requests');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $item) {
            $this->assertEquals($master1->id, $item['assigned_to']);
        }
    }

    public function test_master_can_take_assigned_request_to_in_progress(): void
    {
        $master = User::factory()->master()->create();

        $request = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        $response = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/take");

        $response->assertOk()
            ->assertJsonPath('data.id', $request->id)
            ->assertJsonPath('data.status', Request::STATUS_IN_PROGRESS);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_second_parallel_take_gets_conflict(): void
    {
        $master = User::factory()->master()->create();

        $request = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        // Первый «параллельный» запрос успешно переводит заявку в in_progress.
        $first = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/take");
        $first->assertOk();

        // Второй запрос, пришедший чуть позже, имитирует гонку:
        // он видит уже обновлённое состояние и получает 409.
        $second = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/take");

        $second->assertStatus(409);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_IN_PROGRESS,
            'assigned_to' => $master->id,
        ]);
    }

    public function test_master_cannot_take_not_assigned_or_wrong_status(): void
    {
        $master = User::factory()->master()->create();
        $otherMaster = User::factory()->master()->create();

        $notAssigned = Request::factory()->create([
            'assigned_to' => null,
            'status' => Request::STATUS_NEW,
        ]);

        $assignedToOther = Request::factory()->create([
            'assigned_to' => $otherMaster->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        $inProgress = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$notAssigned->id}/take")
            ->assertStatus(409);

        $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$assignedToOther->id}/take")
            ->assertStatus(409);

        $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$inProgress->id}/take")
            ->assertStatus(409);
    }

    public function test_master_can_complete_in_progress_request(): void
    {
        $master = User::factory()->master()->create();

        $request = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_IN_PROGRESS,
        ]);

        $response = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/complete");

        $response->assertOk()
            ->assertJsonPath('data.id', $request->id)
            ->assertJsonPath('data.status', Request::STATUS_DONE);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => Request::STATUS_DONE,
        ]);
    }

    public function test_master_cannot_complete_not_in_progress_or_other_masters_request(): void
    {
        $master = User::factory()->master()->create();
        $otherMaster = User::factory()->master()->create();

        $assigned = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        $otherRequest = Request::factory()->create([
            'assigned_to' => $otherMaster->id,
            'status' => Request::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$assigned->id}/complete")
            ->assertStatus(409);

        $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$otherRequest->id}/complete")
            ->assertStatus(409);
    }
}

