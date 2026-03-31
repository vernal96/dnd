<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/',
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
            'hero_name' => 'Альрик',
            'email' => 'hero@example.com',
            'password' => 'secret-pass',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.name', 'Альрик')
            ->assertJsonPath('user.email', 'hero@example.com');

        $this->getJson('/api/auth/session', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.email', 'hero@example.com');
    }

    /**
     * Проверяет, что пользователь может войти и завершить сессию.
     */
    public function test_user_can_login_and_logout_via_session_api(): void
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
            'email' => 'gm@example.com',
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
}
