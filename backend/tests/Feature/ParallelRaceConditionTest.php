<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParallelRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что RequestService::masterTake использует lockForUpdate.
     */
    public function test_master_take_uses_lock_for_update(): void
    {
        $servicePath = dirname(__DIR__, 2) . '/app/Services/RequestService.php';
        $source = file_get_contents($servicePath);

        $this->assertStringContainsString(
            'lockForUpdate',
            $source,
            'RequestService must use lockForUpdate for race condition protection'
        );
    }

    /**
     * Два последовательных take — второй получает 409 (имитация race).
     */
    public function test_second_take_returns_409_when_request_already_taken(): void
    {
        $master = User::factory()->master()->create();

        $request = Request::factory()->create([
            'assigned_to' => $master->id,
            'status' => Request::STATUS_ASSIGNED,
        ]);

        $first = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/take");
        $first->assertOk();

        $second = $this->actingAs($master, 'sanctum')
            ->postJson("/api/master/requests/{$request->id}/take");
        $second->assertStatus(409);
    }
}
