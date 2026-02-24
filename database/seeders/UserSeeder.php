<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['name' => 'Dispatcher'],
            ['role' => User::ROLE_DISPATCHER]
        );

        User::query()->updateOrCreate(
            ['name' => 'Master 1'],
            ['role' => User::ROLE_MASTER]
        );

        User::query()->updateOrCreate(
            ['name' => 'Master 2'],
            ['role' => User::ROLE_MASTER]
        );
    }
}
