<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->userName(),
            'role' => User::ROLE_MASTER,
        ];
    }

    public function dispatcher(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_DISPATCHER]);
    }

    public function master(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_MASTER]);
    }
}
