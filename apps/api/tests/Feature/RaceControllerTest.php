<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет API справочника рас и подрас.
 */
final class RaceControllerTest extends TestCase
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
     * Аутентифицирует пользователя через session API и возвращает CSRF токен.
     */
    private function authenticate(User $user): string
    {
        $sessionResponse = $this->getJson('/api/auth/session', $this->frontendHeaders());
        $csrfToken = (string) $sessionResponse->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'secret-pass',
            'remember' => false,
        ])->assertOk();

        return (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');
    }

    /**
     * Проверяет, что API возвращает кодовый справочник рас и подрас.
     */
    public function test_user_can_get_races_with_subraces(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/races', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('0.code', 'human')
            ->assertJsonPath('0.name', 'Человек')
            ->assertJsonPath('0.abilityBonuses.str', 0)
            ->assertJsonPath('0.abilityBonuses.cha', 0)
            ->assertJsonPath('0.abilityBonusChoices', [])
            ->assertJsonPath('0.subraces.0.code', 'standard-human')
            ->assertJsonPath('0.subraces.0.abilityBonuses.str', 1)
            ->assertJsonPath('0.subraces.0.abilityBonuses.cha', 1)
            ->assertJsonPath('0.subraces.1.code', 'variant-human')
            ->assertJsonPath('0.subraces.1.abilityBonuses.str', 0)
            ->assertJsonPath('0.subraces.1.abilityBonusChoices.0.count', 2)
            ->assertJsonPath('1.code', 'elf')
            ->assertJsonPath('1.abilityBonuses.dex', 2)
            ->assertJsonPath('1.subraces.0.code', 'high-elf')
            ->assertJsonPath('1.subraces.0.abilityBonuses.int', 1)
            ->assertJsonPath('1.subraces.1.code', 'wood-elf')
            ->assertJsonPath('1.subraces.1.abilityBonuses.wis', 1)
            ->assertJsonPath('1.subraces.2.code', 'drow')
            ->assertJsonPath('2.code', 'dwarf')
            ->assertJsonPath('2.abilityBonuses.con', 2)
            ->assertJsonPath('2.subraces.0.abilityBonuses.wis', 1)
            ->assertJsonPath('2.subraces.1.abilityBonuses.str', 2)
            ->assertJsonPath('3.code', 'halfling')
            ->assertJsonPath('3.abilityBonuses.dex', 2)
            ->assertJsonPath('3.subraces.0.abilityBonuses.cha', 1)
            ->assertJsonPath('3.subraces.1.abilityBonuses.con', 1)
            ->assertJsonPath('4.code', 'gnome')
            ->assertJsonPath('4.abilityBonuses.int', 2)
            ->assertJsonPath('4.subraces.1.abilityBonuses.con', 1)
            ->assertJsonPath('5.code', 'dragonborn')
            ->assertJsonPath('5.abilityBonuses.str', 2)
            ->assertJsonPath('5.abilityBonuses.cha', 1)
            ->assertJsonPath('6.code', 'half-elf')
            ->assertJsonPath('6.abilityBonuses.cha', 2)
            ->assertJsonPath('6.abilityBonusChoices.0.count', 2)
            ->assertJsonPath('6.abilityBonusChoices.0.abilities.4', 'wis')
            ->assertJsonPath('7.code', 'half-orc')
            ->assertJsonPath('7.abilityBonuses.str', 2)
            ->assertJsonPath('7.abilityBonuses.con', 1)
            ->assertJsonPath('8.code', 'tiefling')
            ->assertJsonPath('8.abilityBonuses.cha', 2)
            ->assertJsonPath('8.abilityBonuses.int', 1);
    }

    /**
     * Проверяет, что пользователь может открыть одну расу по ее коду.
     */
    public function test_user_can_view_one_race_by_code(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/races/dwarf', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('name', 'Дварф')
            ->assertJsonPath('abilityBonuses.con', 2)
            ->assertJsonPath('subraces.0.name', 'Холмовой дварф')
            ->assertJsonPath('subraces.0.abilityBonuses.wis', 1)
            ->assertJsonPath('subraces.1.name', 'Горный дварф')
            ->assertJsonPath('subraces.1.abilityBonuses.str', 2);
    }

    /**
     * Проверяет, что отсутствующая раса недоступна по API.
     */
    public function test_unknown_race_is_not_available(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/races/unknown-race', $this->frontendHeaders())
            ->assertNotFound()
            ->assertJsonPath('message', 'Раса не найдена.');
    }
}
