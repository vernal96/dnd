<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет API справочника классов и подклассов персонажей.
 */
final class CharacterClassControllerTest extends TestCase
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
     * Проверяет, что API возвращает кодовый справочник классов и подклассов персонажей.
     */
    public function test_user_can_get_character_classes_with_subclasses(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/character-classes', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('0.code', 'barbarian')
            ->assertJsonPath('0.skillsByLevel.level1.0.code', 'weapon-mastery')
            ->assertJsonPath('0.skillsByLevel.level1.0.targetType', null)
            ->assertJsonPath('0.startingEquipment.0.item.code', 'longsword')
            ->assertJsonPath('0.startingEquipment.0.item.name', 'Длинный меч')
            ->assertJsonPath('0.defaultPointBuyAllocation.str', 8)
            ->assertJsonPath('0.defaultPointBuyAllocation.con', 8)
            ->assertJsonPath('0.subclasses.0.code', 'path-of-the-berserker')
            ->assertJsonPath('1.code', 'bard')
            ->assertJsonMissingPath('1.skillsByLevel.level7.0.code')
            ->assertJsonPath('2.code', 'cleric')
            ->assertJsonPath('3.code', 'druid')
            ->assertJsonPath('4.code', 'fighter')
            ->assertJsonPath('5.code', 'monk')
            ->assertJsonPath('6.code', 'paladin')
            ->assertJsonPath('7.code', 'ranger')
            ->assertJsonPath('8.code', 'rogue')
            ->assertJsonPath('9.code', 'sorcerer')
            ->assertJsonPath('10.code', 'warlock')
            ->assertJsonPath('11.code', 'wizard')
            ->assertJsonPath('11.subclasses.3.code', 'illusionist');
    }

    /**
     * Проверяет, что пользователь может открыть один класс персонажа по его коду.
     */
    public function test_user_can_view_one_character_class_by_code(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/character-classes/warlock', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('name', 'Колдун / Чернокнижник')
            ->assertJsonPath('skillsByLevel.level1.0.code', 'weapon-mastery')
            ->assertJsonMissingPath('skillsByLevel.level11.0.code')
            ->assertJsonPath('startingEquipment.0.item.name', 'Рюкзак')
            ->assertJsonPath('startingEquipment.0.item.code', 'backpack')
            ->assertJsonPath('defaultPointBuyAllocation.cha', 8)
            ->assertJsonPath('defaultPointBuyAllocation.con', 4)
            ->assertJsonPath('subclasses.0.name', 'Архифея-покровитель')
            ->assertJsonPath('subclasses.3.name', 'Великий Древний покровитель');
    }

    /**
     * Проверяет, что отсутствующий класс персонажа недоступен по API.
     */
    public function test_unknown_character_class_is_not_available(): void
    {
        $user = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $this->authenticate($user);

        $this->getJson('/api/character-classes/unknown-class', $this->frontendHeaders())
            ->assertNotFound()
            ->assertJsonPath('message', 'Класс персонажа не найден.');
    }
}
