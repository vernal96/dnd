<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет API справочника предметов и снаряжения.
 */
final class ItemControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Возвращает стандартные заголовки запросов нашего frontend.
	 *
	 * @return array{Origin: string, Referer: string, Accept: string}
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
	 * Проверяет, что API возвращает кодовый справочник предметов и снаряжения.
	 */
	public function test_user_can_get_items_catalog(): void
	{
		$user = User::query()->create([
			'name' => 'player-one',
			'email' => 'player@example.com',
			'password' => 'secret-pass',
		]);

		$this->authenticate($user);

		$this->getJson('/api/items', $this->frontendHeaders())
			->assertOk()
			->assertJsonCount(109)
			->assertJsonPath('0.code', 'club')
			->assertJsonPath('0.name', 'Дубина')
			->assertJsonPath('0.type', 'melee-weapon')
			->assertJsonPath('0.category', 'simple-melee-weapon')
			->assertJsonPath('0.damageDice', '1d4')
			->assertJsonPath('0.versatileDamageDice', null)
			->assertJsonPath('0.attackAbilities.0', 'str')
			->assertJsonPath('10.type', 'ranged-weapon')
			->assertJsonPath('10.code', 'light-crossbow')
			->assertJsonPath('10.damageDice', '1d8')
			->assertJsonPath('0.image_url', '/api/item-images/club.png')
			->assertJsonPath('10.attackAbilities.0', 'dex')
			->assertJsonPath('34.code', 'net')
			->assertJsonPath('34.damageDice', null)
			->assertJsonPath('34.attackAbilities', [])
			->assertJsonPath('35.armorClassBase', 11)
			->assertJsonPath('35.armorClassAbility', 'dex')
			->assertJsonPath('35.armorClassAbilityCap', null)
			->assertJsonPath('35.armorClassBonus', null)
			->assertJsonPath('38.armorClassBase', 12)
			->assertJsonPath('38.armorClassAbility', 'dex')
			->assertJsonPath('38.armorClassAbilityCap', 2)
			->assertJsonPath('42.armorClassBase', 15)
			->assertJsonPath('42.armorClassAbilityCap', 2)
			->assertJsonPath('43.armorClassBase', 14)
			->assertJsonPath('46.armorClassBase', 18)
			->assertJsonPath('47.armorClassBonus', 2)
			->assertJsonPath('35.category', 'light-armor')
			->assertJsonPath('48.name', 'Рюкзак')
			->assertJsonPath('80.code', 'healers-kit')
			->assertJsonPath('91.code', 'map-or-scroll-case')
			->assertJsonPath('103.code', 'priest-pack')
			->assertJsonPath('108.code', 'cloak');
	}

	/**
	 * Проверяет, что пользователь может открыть один предмет по его коду.
	 */
	public function test_user_can_view_one_item_by_code(): void
	{
		$user = User::query()->create([
			'name' => 'player-one',
			'email' => 'player@example.com',
			'password' => 'secret-pass',
		]);

		$this->authenticate($user);

		$this->getJson('/api/items/dagger', $this->frontendHeaders())
			->assertOk()
			->assertJsonPath('code', 'dagger')
			->assertJsonPath('name', 'Кинжал')
			->assertJsonPath('type', 'melee-weapon')
			->assertJsonPath('category', 'simple-melee-weapon')
			->assertJsonPath('damageDice', '1d4')
			->assertJsonPath('versatileDamageDice', null)
			->assertJsonPath('attackAbilities.0', 'str')
			->assertJsonPath('attackAbilities.1', 'dex')
			->assertJsonPath('armorClassBase', null)
			->assertJsonPath('armorClassAbility', null)
			->assertJsonPath('armorClassAbilityCap', null)
			->assertJsonPath('armorClassBonus', null)
			->assertJsonPath('description', 'Короткий клинок для ближнего боя и броска. Благодаря балансу подходит как для силы, так и для точных ловких ударов.')
			->assertJsonPath('image_url', '/api/item-images/dagger.png')
			->assertJsonPath('isActive', true);
	}

	/**
	 * Проверяет, что отсутствующий предмет недоступен по API.
	 */
	public function test_unknown_item_is_not_available(): void
	{
		$user = User::query()->create([
			'name' => 'player-one',
			'email' => 'player@example.com',
			'password' => 'secret-pass',
		]);

		$this->authenticate($user);

		$this->getJson('/api/items/unknown-item', $this->frontendHeaders())
			->assertNotFound()
			->assertJsonPath('message', 'Предмет не найден.');
	}
}
