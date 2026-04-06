<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\ActorInstance;
use App\Models\Game;
use App\Models\GameSceneState;
use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет запуск runtime-сцены и перемещение runtime-акторов мастером.
 */
final class GmRuntimeSceneControllerTest extends TestCase
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
	 * Проверяет, что мастер может запустить сцену и получить runtime-акторов.
	 */
	public function test_game_master_can_activate_runtime_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-runtime',
			'email' => 'gm-runtime@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime scene',
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
			'status' => 'prepared',
			'version' => 1,
		]);

		$actor = Actor::query()->create([
			'gm_user_id' => $gameMaster->id,
			'kind' => 'npc',
			'name' => 'Орк-разведчик',
			'race' => 'orc',
			'character_class' => 'fighter',
			'level' => 2,
			'movement_speed' => 6,
			'base_health' => 18,
			'health_current' => 18,
			'health_max' => 18,
			'image_path' => 'gm-actors/orc-scout.png',
		]);

		$sceneTemplate->actorPlacements()->create([
			'actor_id' => $actor->id,
			'x' => 2,
			'y' => 3,
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/scenes/'.$sceneState->id.'/activate')
			->assertOk()
			->assertJsonPath('game.active_scene_state_id', $sceneState->id)
			->assertJsonPath('actor_instances.0.name', 'Орк-разведчик')
			->assertJsonPath('actor_instances.0.x', 2)
			->assertJsonPath('actor_instances.0.runtime_state.image_url', '/api/gm/actor-images/orc-scout.png');

		$this->assertDatabaseHas('actor_instances', [
			'game_scene_state_id' => $sceneState->id,
			'name' => 'Орк-разведчик',
			'x' => 2,
			'y' => 3,
		]);
	}

	/**
	 * Проверяет, что мастер может переместить runtime-актора по активной сцене.
	 */
	public function test_game_master_can_move_runtime_actor(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-runtime',
			'email' => 'gm-runtime-move@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime move game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime move scene',
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

		$actorInstance = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Орк-разведчик',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$actorInstance->id.'/move', [
			'x' => 4,
			'y' => 2,
		])
			->assertOk()
			->assertJsonPath('x', 4)
			->assertJsonPath('y', 2);

		$this->assertDatabaseHas('actor_instances', [
			'id' => $actorInstance->id,
			'x' => 4,
			'y' => 2,
		]);
	}

	/**
	 * Проверяет, что мастер может запустить encounter для runtime-акторов сцены.
	 */
	public function test_game_master_can_start_encounter_for_runtime_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-encounter',
			'email' => 'gm-encounter@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Encounter game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Encounter scene',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'active',
			'version' => 7,
			'loaded_at' => now(),
		]);

		$game->forceFill([
			'active_scene_state_id' => $sceneState->id,
		])->save();

		ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Орк',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'runtime_state' => [
				'movement_speed' => 6,
				'stats' => ['dex' => 12],
			],
		]);

		ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Гоблин',
			'status' => 'active',
			'x' => 2,
			'y' => 2,
			'hp_current' => 9,
			'hp_max' => 9,
			'runtime_state' => [
				'movement_speed' => 8,
				'stats' => ['dex' => 16],
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/encounter/start', [])
			->assertOk()
			->assertJsonPath('encounter.status', 'active')
			->assertJsonPath('encounter.round', 1)
			->assertJsonCount(2, 'encounter.participants');

		$this->assertDatabaseCount('encounter_participants', 2);
		$this->assertDatabaseHas('encounters', [
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'status' => 'active',
		]);
	}

	/**
	 * Проверяет, что runtime-сцена мастера открывается даже если active_scene_state_id у игры потерян.
	 */
	public function test_game_master_can_open_active_scene_when_pointer_is_missing(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-refresh',
			'email' => 'gm-runtime-refresh@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime refresh game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
			'active_scene_state_id' => null,
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime refresh scene',
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
			'version' => 3,
			'loaded_at' => now(),
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->getJson('/api/games/'.$game->id.'/runtime/scene')
			->assertOk()
			->assertJsonPath('id', $sceneState->id)
			->assertJsonPath('game.active_scene_state_id', $sceneState->id);

		$this->assertDatabaseHas('games', [
			'id' => $game->id,
			'active_scene_state_id' => $sceneState->id,
		]);
	}
}
