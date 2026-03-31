<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * Обеспечивает наличие локальных dev-пользователей в базе данных.
 */
final class LocalDevelopmentUserService
{
    /**
     * Создает или обновляет локальных пользователей для разработки.
     *
     * @throws Throwable Если сохранение пользователей завершилось технической ошибкой.
     */
    public function ensureUsers(): void
    {
        $this->ensurePlayerUser();
        $this->ensureGameMasterUser();
    }

    /**
     * Создает или обновляет локального пользователя-игрока.
     *
     * @throws Throwable Если сохранение пользователя завершилось технической ошибкой.
     */
    private function ensurePlayerUser(): void
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
    }

    /**
     * Создает или обновляет локального пользователя-мастера.
     *
     * @throws Throwable Если сохранение пользователя завершилось технической ошибкой.
     */
    private function ensureGameMasterUser(): void
    {
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
    }
}
