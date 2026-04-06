<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameInvitation;
use App\Models\GameMember;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет принятие приглашения игроком с выбором свободного персонажа.
 */
final class GameInvitationControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Возвращает стандартные заголовки запросов frontend.
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
	 * Проверяет, что игрок принимает приглашение выбранным свободным персонажем.
	 */
	public function test_player_can_accept_invitation_with_selected_available_character(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-invite',
			'email' => 'gm-invite@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-invite',
			'email' => 'player-invite@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Invite game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Элиан',
			'race' => 'elf',
			'subrace' => 'wood-elf',
			'class' => 'ranger',
			'level' => 2,
			'experience' => 300,
			'status' => 'active',
			'base_stats' => [
				'str' => 9,
				'dex' => 16,
				'con' => 13,
				'int' => 11,
				'wis' => 14,
				'cha' => 10,
			],
			'derived_stats' => [
				'str' => 9,
				'dex' => 16,
				'con' => 13,
				'int' => 11,
				'wis' => 14,
				'cha' => 10,
			],
		]);

		$invitation = GameInvitation::query()->create([
			'game_id' => $game->id,
			'gm_user_id' => $gameMaster->id,
			'invited_user_id' => $player->id,
			'token' => 'accept-token-1',
			'status' => 'pending',
			'sent_at' => now(),
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/game-invitations/'.$invitation->token.'/characters')
			->assertOk()
			->assertJsonCount(1)
			->assertJsonPath('0.id', $character->id);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/game-invitations/'.$invitation->token.'/accept', [
			'character_id' => $character->id,
		])
			->assertOk()
			->assertJsonPath('status', 'accepted');

		$this->assertDatabaseHas('game_members', [
			'game_id' => $game->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
		]);
	}

	/**
	 * Проверяет, что нельзя принять приглашение персонажем, который уже участвует в другой игре.
	 */
	public function test_player_cannot_accept_invitation_with_busy_character(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-invite',
			'email' => 'gm-invite-busy@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-invite',
			'email' => 'player-invite-busy@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$busyGame = Game::query()->create([
			'title' => 'Busy game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$targetGame = Game::query()->create([
			'title' => 'Target game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'draft',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Мира',
			'race' => 'human',
			'class' => 'wizard',
			'level' => 2,
			'experience' => 280,
			'status' => 'active',
			'base_stats' => [
				'str' => 8,
				'dex' => 12,
				'con' => 13,
				'int' => 17,
				'wis' => 12,
				'cha' => 10,
			],
			'derived_stats' => [
				'str' => 8,
				'dex' => 12,
				'con' => 13,
				'int' => 17,
				'wis' => 12,
				'cha' => 10,
			],
		]);

		GameMember::query()->create([
			'game_id' => $busyGame->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		$invitation = GameInvitation::query()->create([
			'game_id' => $targetGame->id,
			'gm_user_id' => $gameMaster->id,
			'invited_user_id' => $player->id,
			'token' => 'accept-token-2',
			'status' => 'pending',
			'sent_at' => now(),
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/game-invitations/'.$invitation->token.'/characters')
			->assertOk()
			->assertJsonCount(0);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/game-invitations/'.$invitation->token.'/accept', [
			'character_id' => $character->id,
		])
			->assertStatus(422)
			->assertJsonPath('message', 'Этот персонаж уже участвует в игре "Busy game".');
	}
}
