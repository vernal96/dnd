<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Game\RandomDiceRollerService;
use App\Domain\Actor\Dice;
use App\Domain\Actor\LuckScale;
use App\Models\Actor;
use App\Models\ActorInstance;
use App\Models\Encounter;
use App\Models\EncounterParticipant;
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
	 * Подменяет сервис броска кубика предсказуемой последовательностью.
	 *
	 * @param list<int> $rolls
	 */
	private function fakeDiceRolls(array $rolls): void
	{
		$this->app->instance(RandomDiceRollerService::class, new class($rolls) extends RandomDiceRollerService
		{
			/**
			 * @param list<int> $rolls
			 */
			public function __construct(
				private array $rolls,
			)
			{
			}

			/**
			 * Выполняет предсказуемый бросок.
			 */
			public function roll(Dice $dice, LuckScale $luckScale): int
			{
				return array_shift($this->rolls) ?? 1;
			}
		});
	}

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
	 * Проверяет, что активация сцены сбрасывает ранее активное сражение.
	 */
	public function test_game_master_activate_scene_resolves_previous_encounter(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-reset-encounter',
			'email' => 'gm-runtime-reset-encounter@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime reset encounter game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime reset encounter scene',
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
			'name' => 'Старый орк',
			'level' => 2,
			'movement_speed' => 6,
			'base_health' => 18,
			'health_current' => 18,
			'health_max' => 18,
		]);

		$sceneTemplate->actorPlacements()->create([
			'actor_id' => $actor->id,
			'x' => 2,
			'y' => 3,
		]);

		$actorInstance = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Старый орк',
			'status' => 'active',
			'x' => 2,
			'y' => 3,
			'hp_current' => 18,
			'hp_max' => 18,
			'luck' => 'normal',
			'runtime_state' => [
				'movement_speed' => 6,
			],
		]);

		$encounter = Encounter::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'status' => 'active',
			'round' => 2,
			'started_at' => now(),
		]);

		$participant = EncounterParticipant::query()->create([
			'encounter_id' => $encounter->id,
			'actor_id' => $actorInstance->id,
			'initiative' => 12,
			'turn_order' => 1,
			'joined_round' => 1,
			'movement_left' => 6,
			'action_available' => true,
			'bonus_action_available' => true,
			'reaction_available' => true,
			'combat_result_state' => 'active',
		]);

		$encounter->forceFill([
			'current_participant_id' => $participant->id,
		])->save();

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/scenes/'.$sceneState->id.'/activate')
			->assertOk()
			->assertJsonPath('encounter', null);

		$this->assertDatabaseHas('encounters', [
			'id' => $encounter->id,
			'status' => 'resolved',
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
	 * Проверяет, что мастер может выполнить атаку оружием runtime-актором.
	 */
	public function test_game_master_can_perform_weapon_attack(): void
	{
		$this->fakeDiceRolls([15, 4]);

		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-attack',
			'email' => 'gm-runtime-attack@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime attack game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime attack scene',
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

		$attacker = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Воин',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'luck' => 'normal',
			'runtime_state' => [
				'level' => 1,
				'armor_class' => 10,
				'movement_speed' => 3,
				'stats' => ['str' => 14, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10],
				'inventory' => [
					[
						'itemCode' => 'longsword',
						'quantity' => 1,
						'slot' => 'main_hand',
						'isEquipped' => true,
						'state' => null,
					],
				],
			],
		]);

		$target = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Цель',
			'status' => 'active',
			'x' => 2,
			'y' => 1,
			'hp_current' => 20,
			'hp_max' => 20,
			'luck' => 'normal',
			'runtime_state' => [
				'armor_class' => 10,
				'movement_speed' => 3,
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$attacker->id.'/actions', [
			'action' => 'weapon_attack',
			'equipment_slot' => 'main_hand',
			'target_actor_id' => $target->id,
		])
			->assertOk()
			->assertJsonPath('actor_instances.1.hp_current', 15)
			->assertJsonPath('runtime_state.action_log.0.type', 'weapon_attack')
			->assertJsonPath('runtime_state.action_log.0.is_hit', true)
			->assertJsonPath('runtime_state.action_log.0.damage', 5);

		$this->assertDatabaseHas('actor_instances', [
			'id' => $target->id,
			'hp_current' => 15,
		]);
	}

	/**
	 * Проверяет, что мастер может экипировать предмет runtime-актору.
	 */
	public function test_game_master_can_equip_runtime_actor_item(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-equip',
			'email' => 'gm-runtime-equip@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime equip game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime equip scene',
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
			'name' => 'Воин экипировки',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'runtime_state' => [
				'inventory' => [
					[
						'itemCode' => 'longsword',
						'quantity' => 1,
						'slot' => null,
						'isEquipped' => false,
						'state' => null,
					],
					[
						'itemCode' => 'chain-mail',
						'quantity' => 1,
						'slot' => null,
						'isEquipped' => false,
						'state' => null,
					],
				],
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$actorInstance->id.'/equipment', [
			'item_code' => 'longsword',
			'slot' => 'main_hand',
		])
			->assertOk()
			->assertJsonPath('actor_instances.0.runtime_state.inventory.0.slot', 'main_hand')
			->assertJsonPath('actor_instances.0.runtime_state.inventory.0.isEquipped', true);

		$actorInstance->refresh();

		$this->assertSame('main_hand', $actorInstance->runtime_state['inventory'][0]['slot']);
		$this->assertTrue($actorInstance->runtime_state['inventory'][0]['isEquipped']);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$actorInstance->id.'/equipment', [
			'item_code' => 'chain-mail',
			'slot' => 'armor',
		])
			->assertOk()
			->assertJsonPath('actor_instances.0.runtime_state.inventory.1.slot', 'armor')
			->assertJsonPath('actor_instances.0.runtime_state.armor_class', 16);

		$actorInstance->refresh();

		$this->assertSame('armor', $actorInstance->runtime_state['inventory'][1]['slot']);
		$this->assertSame(16, $actorInstance->runtime_state['armor_class']);
	}

	/**
	 * Проверяет, что прием сбивания с ног накладывает эффект при провале спасброска.
	 */
	public function test_game_master_can_perform_trip_attack(): void
	{
		$this->fakeDiceRolls([3]);

		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-trip',
			'email' => 'gm-runtime-trip@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime trip game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime trip scene',
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

		$attacker = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Боец',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'luck' => 'normal',
			'runtime_state' => [
				'level' => 1,
				'stats' => ['str' => 14, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10],
			],
		]);

		$target = ActorInstance::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'kind' => 'npc',
			'controller_type' => 'gm',
			'name' => 'Цель',
			'status' => 'active',
			'x' => 2,
			'y' => 1,
			'hp_current' => 20,
			'hp_max' => 20,
			'luck' => 'normal',
			'runtime_state' => [
				'stats' => ['str' => 8, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10],
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$attacker->id.'/actions', [
			'action' => 'trip_attack',
			'target_actor_id' => $target->id,
		])
			->assertOk()
			->assertJsonPath('runtime_state.action_log.0.type', 'trip_attack')
			->assertJsonPath('runtime_state.action_log.0.is_failed', true)
			->assertJsonPath('actor_instances.1.temporary_effects.0.code', 'prone');

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$target->id.'/move', [
			'x' => 3,
			'y' => 1,
		])->assertUnprocessable();
	}

	/**
	 * Проверяет, что поверхность огня наносит уменьшенный расой урон после перемещения.
	 */
	public function test_game_master_move_applies_surface_damage_with_race_resistance(): void
	{
		$this->fakeDiceRolls([17, 5]);

		$gameMaster = User::query()->create([
			'name' => 'gm-runtime-damage',
			'email' => 'gm-runtime-damage@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Runtime damage game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Runtime damage scene',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		for ($y = 0; $y < 6; $y++) {
			for ($x = 0; $x < 6; $x++) {
				$sceneTemplate->cells()->create([
					'x' => $x,
					'y' => $y,
					'terrain_type' => $x === 2 && $y === 1 ? 'fire' : 'grass',
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
			'name' => 'Тифлинг-разведчик',
			'status' => 'active',
			'x' => 1,
			'y' => 1,
			'hp_current' => 18,
			'hp_max' => 18,
			'luck' => 'normal',
			'runtime_state' => [
				'movement_speed' => 6,
				'race' => 'tiefling',
			],
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/actors/'.$actorInstance->id.'/move', [
			'x' => 2,
			'y' => 1,
		])
			->assertOk()
			->assertJsonPath('x', 2)
			->assertJsonPath('y', 1)
			->assertJsonPath('hp_current', 15);

		$this->assertDatabaseHas('actor_instances', [
			'id' => $actorInstance->id,
			'x' => 2,
			'y' => 1,
			'hp_current' => 15,
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
	 * Проверяет, что мастер может вручную завершить активное сражение.
	 */
	public function test_game_master_can_end_encounter_for_runtime_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-end-encounter',
			'email' => 'gm-end-encounter@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'End encounter game',
			'gm_user_id' => $gameMaster->id,
			'status' => 'active',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'End encounter scene',
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

		$actorInstance = ActorInstance::query()->create([
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
			'luck' => 'normal',
			'runtime_state' => [
				'movement_speed' => 6,
				'stats' => ['dex' => 12],
			],
		]);

		$encounter = Encounter::query()->create([
			'game_id' => $game->id,
			'game_scene_state_id' => $sceneState->id,
			'status' => 'active',
			'round' => 1,
			'started_at' => now(),
		]);

		$participant = EncounterParticipant::query()->create([
			'encounter_id' => $encounter->id,
			'actor_id' => $actorInstance->id,
			'initiative' => 12,
			'turn_order' => 1,
			'joined_round' => 1,
			'movement_left' => 6,
			'action_available' => true,
			'bonus_action_available' => true,
			'reaction_available' => true,
			'combat_result_state' => 'active',
		]);

		$encounter->forceFill([
			'current_participant_id' => $participant->id,
		])->save();

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/runtime/encounter/end')
			->assertOk()
			->assertJsonPath('encounter', null);

		$this->assertDatabaseHas('encounters', [
			'id' => $encounter->id,
			'status' => 'resolved',
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
