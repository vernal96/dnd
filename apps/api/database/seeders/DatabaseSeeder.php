<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the minimal local development dataset.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Заполняет базу минимальным набором данных для локальной разработки.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            [
                'email' => 'player@tavern.local',
            ],
            [
                'name' => 'player',
                'password' => Hash::make('password'),
                'can_access_gm' => false,
            ],
        );

        User::query()->updateOrCreate(
            [
                'email' => 'gm@tavern.local',
            ],
            [
                'name' => 'gm',
                'password' => Hash::make('password'),
                'can_access_gm' => true,
            ],
        );

        $this->call([
            LocalDevelopmentDemoSeeder::class,
        ]);
    }
}
