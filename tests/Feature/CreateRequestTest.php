<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_request_successfully(): void
    {
        $user = User::factory()->dispatcher()->create();

        $payload = [
            'client_name' => 'Иван Петров',
            'phone' => '+7 (999) 111-22-33',
            'address' => 'ул. Ленина, д. 1, кв. 10',
            'problem_text' => 'Не работает отопление.',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/requests', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'client_name',
                    'phone',
                    'address',
                    'problem_text',
                    'status',
                    'assigned_to',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.client_name', $payload['client_name'])
            ->assertJsonPath('data.phone', $payload['phone'])
            ->assertJsonPath('data.address', $payload['address'])
            ->assertJsonPath('data.problem_text', $payload['problem_text'])
            ->assertJsonPath('data.status', Request::STATUS_NEW)
            ->assertJsonPath('data.assigned_to', null);

        $this->assertDatabaseHas('requests', [
            'client_name' => $payload['client_name'],
            'phone' => $payload['phone'],
            'address' => $payload['address'],
            'problem_text' => $payload['problem_text'],
            'status' => Request::STATUS_NEW,
        ]);
    }
}
