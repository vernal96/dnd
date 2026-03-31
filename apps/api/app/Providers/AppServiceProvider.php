<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

/**
 * Регистрирует и загружает сервисы приложения.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы приложения.
     */
    public function register(): void
    {
        //
    }

    /**
     * Выполняет загрузку сервисов приложения.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(
            /**
             * Формирует ссылку на экран смены пароля во frontend-приложении.
             */
            static function (User $user, string $token): string {
                $applicationUrl = rtrim((string) config('app.url'), '/');

                return sprintf(
                    '%s/reset-password?token=%s&email=%s',
                    $applicationUrl,
                    urlencode($token),
                    urlencode($user->getEmailForPasswordReset()),
                );
            },
        );
    }
}
