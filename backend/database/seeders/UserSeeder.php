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
            ['name' => 'Алексей Смирнов'],
            ['role' => User::ROLE_MASTER]
        );

        User::query()->updateOrCreate(
            ['name' => 'Дмитрий Кузнецов'],
            ['role' => User::ROLE_MASTER]
        );
    }
}
