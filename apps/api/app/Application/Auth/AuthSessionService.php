<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Data\Auth\AuthSessionData;
use App\Data\Auth\LoginCredentialsData;
use App\Data\Auth\RegisterUserData;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Управляет пользовательской сессией для JSON API авторизации.
 */
final readonly class AuthSessionService
{
    /**
     * Создает сервис работы с пользовательской сессией.
     */
    public function __construct(
        private AuthManager $authManager,
    ) {}

    /**
     * Возвращает снимок текущего состояния аутентификации.
     */
    public function getSessionState(Request $request): AuthSessionData
    {
        /** @var User|null $user */
        $user = $request->user('web');

        return AuthSessionData::fromUser($user, $request->session()->token());
    }

    /**
     * Выполняет вход пользователя по email и паролю.
     */
    public function login(LoginCredentialsData $credentials, Request $request): AuthSessionData
    {
        $guard = $this->authManager->guard('web');

        $isAuthenticated = $guard->attempt($credentials->toAuthAttempt(), $credentials->remember);

        if (! $isAuthenticated) {
            throw new RuntimeException('Неверный email или пароль.');
        }

        $request->session()->regenerate();

        /** @var User|null $user */
        $user = $guard->user();

        return AuthSessionData::fromUser($user, $request->session()->token());
    }

    /**
     * Регистрирует нового пользователя и сразу открывает сессию.
     */
    public function register(RegisterUserData $data, Request $request): AuthSessionData
    {
        /** @var User $user */
        $user = DB::transaction(static function () use ($data): User {
            return User::query()->create([
                'name' => $data->heroName,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);
        });

        $guard = $this->authManager->guard('web');
        $guard->login($user);
        $request->session()->regenerate();

        return AuthSessionData::fromUser($user, $request->session()->token());
    }

    /**
     * Завершает текущую пользовательскую сессию.
     */
    public function logout(Request $request): void
    {
        $this->authManager->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
