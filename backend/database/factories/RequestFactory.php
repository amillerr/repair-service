<?php

namespace Database\Factories;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'client_name' => 'Test Client',
            'phone' => '+7 900 000-00-00',
            'address' => 'Test address, 1',
            'problem_text' => 'Test problem',
            'status' => Request::STATUS_NEW,
        ];
    }
}
