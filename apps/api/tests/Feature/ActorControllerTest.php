<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет CRUD API библиотеки persistent-акторов мастера.
 */
final class ActorControllerTest extends TestCase
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
	 * Проверяет, что мастер может создать, просмотреть, обновить и удалить актора.
	 */
	public function test_game_master_can_manage_persistent_actors(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-one',
			'email' => 'gm-one@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$createResponse = $this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/gm/actors', [
			'kind' => 'npc',
			'name' => 'Старый друид',
			'description' => 'Хранитель рощи.',
			'race' => 'elf',
			'character_class' => 'druid',
			'level' => 4,
			'movement_speed' => 6,
			'base_health' => 22,
			'health_current' => 22,
			'health_max' => 22,
			'stats' => [
				'strength' => 10,
				'dexterity' => 14,
				'wisdom' => 17,
			],
			'inventory' => [
				[
					'item_code' => 'longsword',
					'quantity' => 1,
					'is_equipped' => true,
					'slot' => 'main-hand',
				],
				[
					'item_code' => 'backpack',
					'quantity' => 1,
				],
			],
			'image_path' => 'actors/druid.png',
		]);

		$createResponse
			->assertCreated()
			->assertJsonPath('gm_user_id', $gameMaster->id)
			->assertJsonPath('kind', 'npc')
			->assertJsonPath('name', 'Старый друид')
			->assertJsonPath('race', 'elf')
			->assertJsonPath('character_class', 'druid')
			->assertJsonPath('level', 4)
			->assertJsonPath('movement_speed', 6)
			->assertJsonPath('base_health', 22)
			->assertJsonPath('health_current', 22)
			->assertJsonPath('health_max', 22)
			->assertJsonPath('stats.wisdom', 17)
			->assertJsonPath('inventory.0.itemCode', 'longsword')
			->assertJsonPath('inventory.0.isEquipped', true)
			->assertJsonPath('image_path', 'actors/druid.png')
			->assertJsonPath('image_url', '/api/gm/actor-images/druid.png');

		$actorId = (int) $createResponse->json('id');

		$this->getJson('/api/gm/actors', $this->frontendHeaders())
			->assertOk()
			->assertJsonCount(1)
			->assertJsonPath('0.id', $actorId)
			->assertJsonPath('0.name', 'Старый друид');

		$this->getJson('/api/gm/actors/'.$actorId, $this->frontendHeaders())
			->assertOk()
			->assertJsonPath('id', $actorId)
			->assertJsonPath('inventory.1.itemCode', 'backpack');

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->putJson('/api/gm/actors/'.$actorId, [
			'kind' => 'npc',
			'name' => 'Старший друид',
			'description' => 'Обновленное описание.',
			'race' => 'elf',
			'character_class' => 'druid',
			'level' => 5,
			'movement_speed' => 7,
			'base_health' => 30,
			'health_current' => 28,
			'health_max' => 30,
			'stats' => [
				'strength' => 10,
				'dexterity' => 14,
				'wisdom' => 18,
			],
			'inventory' => [
				[
					'item_code' => 'longsword',
					'quantity' => 1,
					'is_equipped' => true,
					'slot' => 'main-hand',
				],
				[
					'item_code' => 'chain-mail',
					'quantity' => 2,
				],
			],
			'image_path' => 'actors/druid-elder.png',
			'meta' => [
				'notes' => 'Носитель древнего знания',
			],
		])
			->assertOk()
			->assertJsonPath('name', 'Старший друид')
			->assertJsonPath('level', 5)
			->assertJsonPath('movement_speed', 7)
			->assertJsonPath('health_max', 30)
			->assertJsonPath('inventory.1.itemCode', 'chain-mail')
			->assertJsonPath('image_url', '/api/gm/actor-images/druid-elder.png')
			->assertJsonPath('meta.notes', 'Носитель древнего знания');

		$this->assertDatabaseHas('actors', [
			'id' => $actorId,
			'gm_user_id' => $gameMaster->id,
			'name' => 'Старший друид',
			'level' => 5,
			'image_path' => 'actors/druid-elder.png',
		]);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->deleteJson('/api/gm/actors/'.$actorId)
			->assertNoContent();

		$this->assertDatabaseMissing('actors', [
			'id' => $actorId,
		]);
	}

	/**
	 * Проверяет, что мастер не может работать с чужими акторами.
	 */
	public function test_game_master_cannot_access_foreign_game_actors(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-one',
			'email' => 'gm-one@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$foreignGameMaster = User::query()->create([
			'name' => 'gm-two',
			'email' => 'gm-two@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$actor = Actor::query()->create([
			'gm_user_id' => $foreignGameMaster->id,
			'kind' => 'npc',
			'name' => 'Чужой герой',
			'level' => 2,
			'movement_speed' => 6,
		]);

		$this->authenticate($gameMaster);

		$this->getJson('/api/gm/actors', $this->frontendHeaders())
			->assertOk()
			->assertJsonMissing(['name' => 'Чужой герой']);

		$this->getJson('/api/gm/actors/'.$actor->id, $this->frontendHeaders())
			->assertNotFound()
			->assertJsonPath('message', 'Актор не найден.');
	}

	/**
	 * Проверяет, что API отклоняет неизвестные коды предметов в инвентаре.
	 */
	public function test_actor_inventory_item_code_must_exist_in_catalog(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-one',
			'email' => 'gm-one@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/gm/actors', [
			'name' => 'Скаут',
			'movement_speed' => 6,
			'inventory' => [
				[
					'item_code' => 'unknown-item',
					'quantity' => 1,
				],
			],
		])
			->assertUnprocessable()
			->assertJsonValidationErrors(['inventory.0.item_code']);
	}
}
