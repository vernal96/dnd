<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActorInstance;
use App\Models\Encounter;
use App\Models\EncounterParticipant;
use App\Models\Game;
use App\Models\GameMember;
use App\Models\GameSceneState;
use App\Models\PlayerCharacter;
use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет активные игры игрока и доступ к runtime-сцене по участию в игре.
 */
final class PlayerRuntimeSceneControllerTest extends TestCase
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
	 * Проверяет, что игрок видит только игры с уже запущенной сценой.
	 */
	public function test_player_can_list_active_games_with_running_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-player-active',
			'email' => 'gm-player-active@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-active',
			'email' => 'player-active@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Сильва',
			'race' => 'elf',
			'class' => 'ranger',
			'level' => 3,
			'experience' => 900,
			'status' => 'active',
			'base_stats' => ['str' => 9, 'dex' => 16, 'con' => 12, 'int' => 11, 'wis' => 14, 'cha' => 10],
			'derived_stats' => ['str' => 9, 'dex' => 16, 'con' => 12, 'int' => 11, 'wis' => 14, 'cha' => 10, 'speed' => 7],
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Лесная поляна',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		$activeGame = Game::query()->create([
			'title' => 'Активный стол',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$activeSceneState = GameSceneState::query()->create([
			'game_id' => $activeGame->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 1,
			'loaded_at' => now(),
		]);

		$activeGame->forceFill([
			'active_scene_state_id' => $activeSceneState->id,
		])->save();

		GameMember::query()->create([
			'game_id' => $activeGame->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		$inactiveGame = Game::query()->create([
			'title' => 'Неактивный стол',
			'gm_user_id' => $gameMaster->id,
			'status' => 'draft',
		]);

		GameMember::query()->create([
			'game_id' => $inactiveGame->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'inactive',
			'joined_at' => now(),
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/player/games/active')
			->assertOk()
			->assertJsonCount(1)
			->assertJsonPath('0.id', $activeGame->id)
			->assertJsonPath('0.active_scene_state.id', $activeSceneState->id)
			->assertJsonPath('0.members.0.player_character.id', $character->id);
	}

	/**
	 * Проверяет, что игрок может открыть runtime-сцену своей активной игры.
	 */
	public function test_player_can_open_active_runtime_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-player-runtime',
			'email' => 'gm-player-runtime@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-runtime',
			'email' => 'player-runtime@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Тарин',
			'race' => 'human',
			'class' => 'fighter',
			'level' => 2,
			'experience' => 320,
			'status' => 'active',
			'image_path' => 'player-characters/tarin.png',
			'base_stats' => ['str' => 15, 'dex' => 11, 'con' => 14, 'int' => 9, 'wis' => 10, 'cha' => 12],
			'derived_stats' => ['str' => 15, 'dex' => 11, 'con' => 14, 'int' => 9, 'wis' => 10, 'cha' => 12, 'speed' => 6],
		]);

		$game = Game::query()->create([
			'title' => 'Runtime стол игрока',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Таверна',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		for ($y = 0; $y < 6; $y++) {
			for ($x = 0; $x < 6; $x++) {
				$sceneTemplate->cells()->create([
					'x' => $x,
					'y' => $y,
					'terrain_type' => 'grass',
					'elevation' => 0,
					'is_passable' => true,
					'blocks_vision' => false,
				]);
			}
		}

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 2,
			'loaded_at' => now(),
		]);

		$game->forceFill([
			'active_scene_state_id' => $sceneState->id,
		])->save();

		GameMember::query()->create([
			'game_id' => $game->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		$actorInstance = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'player_character_id' => $character->id,
			'controlled_by_user_id' => $player->id,
			'kind' => 'player_character',
			'controller_type' => 'player',
			'name' => 'Тарин',
			'status' => 'active',
			'x' => 2,
			'y' => 1,
			'hp_current' => 16,
			'hp_max' => 16,
			'runtime_state' => [
				'image_url' => '/api/player/character-images/tarin.png',
				'movement_speed' => 6,
			],
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/player/games/'.$game->id.'/runtime/scene')
			->assertOk()
			->assertJsonPath('id', $sceneState->id)
			->assertJsonPath('game.id', $game->id)
			->assertJsonPath('actor_instances.0.id', $actorInstance->id)
			->assertJsonPath('actor_instances.0.controlled_by_user_id', $player->id);
	}

	/**
	 * Проверяет, что игрок может переместить только своего героя в пределах скорости.
	 */
	public function test_player_can_move_owned_runtime_actor(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-player-move',
			'email' => 'gm-player-move@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-move',
			'email' => 'player-move@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Лиора',
			'race' => 'elf',
			'class' => 'rogue',
			'level' => 2,
			'experience' => 280,
			'status' => 'active',
			'base_stats' => ['str' => 9, 'dex' => 17, 'con' => 12, 'int' => 12, 'wis' => 11, 'cha' => 13],
			'derived_stats' => ['str' => 9, 'dex' => 17, 'con' => 12, 'int' => 12, 'wis' => 11, 'cha' => 13, 'speed' => 6],
		]);

		$game = Game::query()->create([
			'title' => 'Игрок двигает героя',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Коридор',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 1,
			'loaded_at' => now(),
		]);

		$game->forceFill([
			'active_scene_state_id' => $sceneState->id,
		])->save();

		GameMember::query()->create([
			'game_id' => $game->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		$actorInstance = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'player_character_id' => $character->id,
			'controlled_by_user_id' => $player->id,
			'kind' => 'player_character',
			'controller_type' => 'player',
			'name' => 'Лиора',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 14,
			'hp_max' => 14,
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/player/games/'.$game->id.'/runtime/actors/'.$actorInstance->id.'/move', [
			'x' => 3,
			'y' => 2,
		])
			->assertOk()
			->assertJsonPath('id', $actorInstance->id)
			->assertJsonPath('x', 3)
			->assertJsonPath('y', 2);

		$this->assertDatabaseHas('actor_instances', [
			'id' => $actorInstance->id,
			'x' => 3,
			'y' => 2,
		]);
	}

	/**
	 * Проверяет, что игрок не может ходить своим героем, пока активен чужой ход в encounter.
	 */
	public function test_player_cannot_move_owned_actor_when_it_is_not_their_turn(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-player-encounter',
			'email' => 'gm-player-encounter@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-encounter',
			'email' => 'player-encounter@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Мира',
			'race' => 'human',
			'class' => 'cleric',
			'level' => 2,
			'experience' => 250,
			'status' => 'active',
			'base_stats' => ['str' => 10, 'dex' => 12, 'con' => 13, 'int' => 10, 'wis' => 16, 'cha' => 11],
			'derived_stats' => ['str' => 10, 'dex' => 12, 'con' => 13, 'int' => 10, 'wis' => 16, 'cha' => 11, 'speed' => 6],
		]);

		$game = Game::query()->create([
			'title' => 'Player encounter game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Боевой коридор',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 2,
			'loaded_at' => now(),
		]);

		$game->forceFill([
			'active_scene_state_id' => $sceneState->id,
		])->save();

		GameMember::query()->create([
			'game_id' => $game->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		$playerActor = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'player_character_id' => $character->id,
			'controlled_by_user_id' => $player->id,
			'kind' => 'player_character',
			'controller_type' => 'player',
			'name' => 'Мира',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 14,
			'hp_max' => 14,
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$enemyActor = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Скелет',
			'status' => 'active',
			'x' => 3,
			'y' => 1,
			'hp_current' => 10,
			'hp_max' => 10,
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$encounter = Encounter::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'status' => 'active',
			'round' => 1,
			'started_at' => now(),
		]);

		$enemyParticipant = EncounterParticipant::query()->create([
			'encounter_id' => $encounter->id,
			'actor_id' => $enemyActor->id,
			'initiative' => 18,
			'turn_order' => 1,
			'joined_round' => 1,
			'movement_left' => 6,
			'action_available' => true,
			'bonus_action_available' => true,
			'reaction_available' => true,
			'combat_result_state' => 'active',
		]);

		EncounterParticipant::query()->create([
			'encounter_id' => $encounter->id,
			'actor_id' => $playerActor->id,
			'initiative' => 12,
			'turn_order' => 2,
			'joined_round' => 1,
			'movement_left' => 6,
			'action_available' => true,
			'bonus_action_available' => true,
			'reaction_available' => true,
			'combat_result_state' => 'active',
		]);

		$encounter->forceFill([
			'current_participant_id' => $enemyParticipant->id,
		])->save();

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/player/games/'.$game->id.'/runtime/actors/'.$playerActor->id.'/move', [
			'x' => 2,
			'y' => 1,
		])
			->assertStatus(422)
			->assertJsonPath('message', 'Сейчас не твой ход.');

		$this->assertDatabaseHas('actor_instances', [
			'id' => $playerActor->id,
			'x' => 1,
			'y' => 1,
		]);
	}

	/**
	 * Проверяет, что игрок может открыть активную runtime-сцену даже если pointer игры потерян.
	 */
	public function test_player_can_open_active_runtime_scene_when_pointer_is_missing(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-player-runtime-refresh',
			'email' => 'gm-player-runtime-refresh@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$player = User::query()->create([
			'name' => 'player-runtime-refresh',
			'email' => 'player-runtime-refresh@example.com',
			'can_access_gm' => false,
			'password' => 'secret-pass',
		]);

		$character = PlayerCharacter::query()->create([
			'user_id' => $player->id,
			'name' => 'Нейра',
			'race' => 'elf',
			'class' => 'wizard',
			'level' => 2,
			'experience' => 350,
			'status' => 'active',
			'base_stats' => ['str' => 8, 'dex' => 14, 'con' => 12, 'int' => 16, 'wis' => 11, 'cha' => 10],
			'derived_stats' => ['str' => 8, 'dex' => 14, 'con' => 12, 'int' => 16, 'wis' => 11, 'cha' => 10, 'speed' => 6],
		]);

		$game = Game::query()->create([
			'title' => 'Runtime refresh player table',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
			'active_scene_state_id' => null,
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Башня мага',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		for ($y = 0; $y < 6; $y++) {
			for ($x = 0; $x < 6; $x++) {
				$sceneTemplate->cells()->create([
					'x' => $x,
					'y' => $y,
					'terrain_type' => 'grass',
					'elevation' => 0,
					'is_passable' => true,
					'blocks_vision' => false,
				]);
			}
		}

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 4,
			'loaded_at' => now(),
		]);

		GameMember::query()->create([
			'game_id' => $game->id,
			'user_id' => $player->id,
			'player_character_id' => $character->id,
			'role' => 'player',
			'status' => 'active',
			'joined_at' => now(),
		]);

		ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'player_character_id' => $character->id,
			'controlled_by_user_id' => $player->id,
			'kind' => 'player_character',
			'controller_type' => 'player',
			'name' => 'Нейра',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 12,
			'hp_max' => 12,
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$csrfToken = $this->authenticate($player);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/player/games/'.$game->id.'/runtime/scene')
			->assertOk()
			->assertJsonPath('id', $sceneState->id)
			->assertJsonPath('game.active_scene_state_id', $sceneState->id);

		$this->assertDatabaseHas('games', [
			'id' => $game->id,
			'active_scene_state_id' => $sceneState->id,
		]);
	}
}
