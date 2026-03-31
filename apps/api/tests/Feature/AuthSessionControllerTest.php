<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Проверяет сценарии входа, регистрации и завершения сессии через auth API.
 */
final class AuthSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Возвращает стандартные заголовки запросов нашего frontend.
     *
     * @return array{Origin:string,Referer:string,Accept:string}
     */
    private function frontendHeaders(): array
    {
        return [
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Проверяет, что пользователь может зарегистрироваться и сразу получить сессию.
     */
    public function test_user_can_register_and_receive_session_state(): void
    {
        $sessionResponse = $this->getJson('/api/auth/session', $this->frontendHeaders());
        $csrfToken = (string) $sessionResponse->json('csrfToken');

        $response = $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/register', [
            'login' => 'alrik',
            'email' => 'hero@example.com',
            'password' => 'secret-pass',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.name', 'alrik')
            ->assertJsonPath('user.email', 'hero@example.com');

        $this->getJson('/api/auth/session', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.email', 'hero@example.com');
    }

    /**
     * Проверяет, что пользователь может войти по email и завершить сессию.
     */
    public function test_user_can_login_by_email_and_logout_via_session_api(): void
    {
        User::query()->create([
            'name' => 'Мастер подземелий',
            'email' => 'gm@example.com',
            'password' => 'secret-pass',
        ]);

        $sessionResponse = $this->getJson('/api/auth/session', $this->frontendHeaders());
        $csrfToken = (string) $sessionResponse->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/login', [
            'login' => 'gm@example.com',
            'password' => 'secret-pass',
            'remember' => true,
        ])
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.email', 'gm@example.com');

        $logoutToken = (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $logoutToken,
        ])->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->getJson('/api/auth/session', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('user', null);
    }

    /**
     * Проверяет, что пользователь может войти по логину.
     */
    public function test_user_can_login_by_login(): void
    {
        User::query()->create([
            'name' => 'archmage',
            'email' => 'mage@example.com',
            'password' => 'secret-pass',
        ]);

        $csrfToken = (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/login', [
            'login' => 'archmage',
            'password' => 'secret-pass',
            'remember' => false,
        ])
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.name', 'archmage');
    }

    /**
     * Проверяет, что API не обслуживает чужой browser-origin.
     */
    public function test_session_api_rejects_untrusted_origin(): void
    {
        $this->getJson('/api/auth/session', [
            'Origin' => 'http://malicious.example',
            'Referer' => 'http://malicious.example/',
            'Accept' => 'application/json',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Доступ к API разрешен только для доверенного frontend-origin.');
    }

    /**
     * Проверяет, что пользователь может запросить восстановление пароля.
     */
    public function test_user_can_request_password_reset(): void
    {
        Notification::fake();

        $user = User::query()->create([
            'name' => 'player',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $csrfToken = (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/forgot-password', [
            'email' => 'player@example.com',
        ])
            ->assertAccepted()
            ->assertJsonPath('message', 'Если учетная запись существует, инструкции по восстановлению уже отправлены.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * Проверяет, что пользователь может завершить сброс пароля по токену.
     */
    public function test_user_can_reset_password_by_token(): void
    {
        $user = User::query()->create([
            'name' => 'player',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $token = Password::broker()->createToken($user);
        $csrfToken = (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'player@example.com',
            'password' => 'new-secret-pass',
            'password_confirmation' => 'new-secret-pass',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Пароль обновлен. Теперь можно войти с новым паролем.');

        $loginToken = (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $loginToken,
        ])->postJson('/api/auth/login', [
            'login' => 'player@example.com',
            'password' => 'new-secret-pass',
            'remember' => false,
        ])
            ->assertOk()
            ->assertJsonPath('authenticated', true);
    }
}
