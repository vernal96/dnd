<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Проверяет API персонажей игрока.
 */
final class PlayerCharacterControllerTest extends TestCase
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
	 * Проверяет, что игрок может создать персонажа и увидеть его в списке.
	 */
	public function test_player_can_create_character(): void
	{
		Storage::fake('game_images');

		$user = User::query()->create([
			'name' => 'player-one',
			'email' => 'player@example.com',
			'password' => 'secret-pass',
		]);

		Storage::disk('game_images')->put('player-characters/' . $user->id . '/portrait.png', 'fake-image-content');

		$csrfToken = $this->authenticate($user);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/player/characters', [
			'name' => 'Эландриэль',
			'description' => 'Следопыт из лесов.',
			'race' => 'elf',
			'subrace' => 'wood-elf',
			'character_class' => 'ranger',
			'image_path' => 'player-characters/' . $user->id . '/portrait.png',
			'base_stats' => [
				'str' => 5,
				'dex' => 10,
				'con' => 5,
				'int' => 5,
				'wis' => 8,
				'cha' => 5,
			],
		])
			->assertCreated()
			->assertJsonPath('name', 'Эландриэль')
			->assertJsonPath('race', 'elf')
			->assertJsonPath('subrace', 'wood-elf')
			->assertJsonPath('character_class', 'ranger')
			->assertJsonPath('level', 1)
			->assertJsonPath('experience', 0)
			->assertJsonPath('base_stats.dex', 10)
			->assertJsonPath('derived_stats.dex', 10)
			->assertJsonPath('derived_stats.wis', 8)
			->assertJsonPath('derived_stats.speed', 6)
			->assertJsonPath('derived_stats.health', 0)
			->assertJsonPath('image_url', '/api/player/character-images/portrait.png');

		$this->assertDatabaseHas('player_characters', [
			'user_id' => $user->id,
			'name' => 'Эландриэль',
			'race' => 'elf',
			'subrace' => 'wood-elf',
			'class' => 'ranger',
		]);

		$this->getJson('/api/player/characters', $this->frontendHeaders())
			->assertOk()
			->assertJsonPath('0.name', 'Эландриэль')
			->assertJsonPath('0.subrace_name', 'Лесной эльф');
	}

	/**
	 * Проверяет, что персонаж нельзя создать с неверным бюджетом характеристик.
	 */
	public function test_player_character_requires_exact_point_buy_budget(): void
	{
		$user = User::query()->create([
			'name' => 'player-two',
			'email' => 'player-two@example.com',
			'password' => 'secret-pass',
		]);

		$csrfToken = $this->authenticate($user);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/player/characters', [
			'name' => 'Ошибка',
			'race' => 'human',
			'subrace' => 'standard-human',
			'character_class' => 'fighter',
			'base_stats' => [
				'str' => 2,
				'dex' => 2,
				'con' => 2,
				'int' => 2,
				'wis' => 2,
				'cha' => 2,
			],
		])
			->assertStatus(422)
			->assertJsonValidationErrors(['base_stats']);
	}

	/**
	 * Проверяет, что игрок может сменить фото уже созданного персонажа.
	 */
	public function test_player_can_update_character_image(): void
	{
		Storage::fake('game_images');

		$user = User::query()->create([
			'name' => 'player-three',
			'email' => 'player-three@example.com',
			'password' => 'secret-pass',
		]);

		Storage::disk('game_images')->put('player-characters/' . $user->id . '/portrait-new.png', 'fake-image-content');

		$character = \App\Models\PlayerCharacter::query()->create([
			'user_id' => $user->id,
			'name' => 'Кел',
			'race' => 'human',
			'subrace' => 'standard-human',
			'class' => 'fighter',
			'level' => 1,
			'experience' => 0,
			'status' => 'active',
			'base_stats' => [
				'str' => 3,
				'dex' => 3,
				'con' => 3,
				'int' => 3,
				'wis' => 3,
				'cha' => 28,
			],
			'derived_stats' => [
				'str' => 3,
				'dex' => 3,
				'con' => 3,
				'int' => 3,
				'wis' => 3,
				'cha' => 28,
			],
		]);

		$csrfToken = $this->authenticate($user);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->patchJson('/api/player/characters/' . $character->id . '/image', [
			'image_path' => 'player-characters/' . $user->id . '/portrait-new.png',
		])
			->assertOk()
			->assertJsonPath('image_path', 'player-characters/' . $user->id . '/portrait-new.png')
			->assertJsonPath('image_url', '/api/player/character-images/portrait-new.png');
	}
}
