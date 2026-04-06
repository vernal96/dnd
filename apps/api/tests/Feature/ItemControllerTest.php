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
			->assertJsonCount(3)
			->assertJsonPath('0.code', 'chain-mail')
			->assertJsonPath('0.type', 'armor')
			->assertJsonPath('0.armorClassBase', 16)
			->assertJsonPath('1.code', 'longsword')
			->assertJsonPath('1.name', 'Длинный меч')
			->assertJsonPath('1.type', 'melee-weapon')
			->assertJsonPath('1.category', 'martial-melee-weapon')
			->assertJsonPath('1.damageDice', '1d8')
			->assertJsonPath('1.versatileDamageDice', '1d10')
			->assertJsonPath('1.attackAbilities.0', 'str')
			->assertJsonPath('2.code', 'backpack')
			->assertJsonPath('2.type', 'equipment')
			->assertJsonPath('2.image_url', '/api/item-images/backpack.png');
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

		$this->getJson('/api/items/chain-mail', $this->frontendHeaders())
			->assertOk()
			->assertJsonPath('code', 'chain-mail')
			->assertJsonPath('name', 'Кольчуга')
			->assertJsonPath('type', 'armor')
			->assertJsonPath('category', 'heavy-armor')
			->assertJsonPath('damageDice', null)
			->assertJsonPath('versatileDamageDice', null)
			->assertJsonPath('attackAbilities', [])
			->assertJsonPath('armorClassBase', 16)
			->assertJsonPath('armorClassAbility', null)
			->assertJsonPath('armorClassAbilityCap', null)
			->assertJsonPath('armorClassBonus', null)
			->assertJsonPath('description', 'Полноценная кольчуга с поддоспешником и рукавицами, хорошо защищающая ценой веса и шума.')
			->assertJsonPath('image_url', '/api/item-images/chain-mail.png')
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
