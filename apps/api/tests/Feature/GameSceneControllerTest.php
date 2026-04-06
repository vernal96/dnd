<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Game;
use App\Models\GameSceneState;
use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет CRUD-сцен внутри игры мастера.
 */
final class GameSceneControllerTest extends TestCase
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
	 * Проверяет, что мастер может создать authored-сцену внутри своей игры.
	 */
	public function test_game_master_can_create_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-scenes',
			'email' => 'gm-scenes@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Моя игра',
			'gm_user_id' => $gameMaster->id,
			'status' => 'draft',
		]);

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->postJson('/api/games/'.$game->id.'/scenes', [
			'name' => 'Вход в руины',
			'width' => 6,
			'height' => 6,
		])
			->assertCreated()
			->assertJsonPath('scene_template.name', 'Вход в руины')
			->assertJsonPath('scene_template.width', 6)
			->assertJsonPath('scene_template.height', 6);

		$this->assertDatabaseHas('scene_templates', [
			'name' => 'Вход в руины',
			'created_by' => $gameMaster->id,
		]);

		$this->assertDatabaseHas('game_scene_states', [
			'game_id' => $game->id,
			'status' => 'prepared',
		]);
	}

	/**
	 * Проверяет, что мастер может сохранить authored-сцену и ее сетку.
	 */
	public function test_game_master_can_update_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-scenes',
			'email' => 'gm-scenes@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Моя игра',
			'gm_user_id' => $gameMaster->id,
			'status' => 'draft',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Старая сцена',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		for ($y = 0; $y < 6; $y++) {
			for ($x = 0; $x < 6; $x++) {
				$sceneTemplate->cells()->create([
					'x' => $x,
					'y' => $y,
					'terrain_type' => 'ground',
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
			'name' => 'Разведчик',
			'race' => 'human',
			'character_class' => 'ranger',
			'level' => 3,
			'movement_speed' => 6,
			'base_health' => 18,
			'health_current' => 18,
			'health_max' => 18,
			'image_path' => 'gm-actors/scout.png',
		]);

		$csrfToken = $this->authenticate($gameMaster);
		$cells = [];

		for ($y = 0; $y < 6; $y++) {
			for ($x = 0; $x < 7; $x++) {
				$cells[] = [
					'x' => $x,
					'y' => $y,
					'terrain_type' => $x === 2 && $y === 3 ? 'stone' : 'grass',
					'elevation' => 0,
					'is_passable' => $x === 2 && $y === 3 ? false : true,
					'blocks_vision' => $x === 2 && $y === 3,
				];
			}
		}

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->putJson('/api/games/'.$game->id.'/scenes/'.$sceneState->id, [
			'name' => 'Новая сцена',
			'description' => 'Редактируемая сцена',
			'width' => 7,
			'height' => 6,
			'metadata' => [
				'viewport' => [
					'offsetX' => 10,
				],
			],
			'objects' => [
				[
					'kind' => 'barrel',
					'name' => 'Бочка',
					'x' => 1,
					'y' => 1,
					'width' => 1,
					'height' => 1,
					'is_hidden' => false,
					'is_interactive' => true,
				],
			],
			'actors' => [
				[
					'actor_id' => $actor->id,
					'x' => 3,
					'y' => 2,
				],
			],
			'cells' => $cells,
		])
			->assertOk()
			->assertJsonPath('scene_template.name', 'Новая сцена')
			->assertJsonPath('scene_template.width', 7)
			->assertJsonPath('scene_template.actor_placements.0.actor.name', 'Разведчик')
			->assertJsonPath('version', 2);

		$this->assertDatabaseHas('scene_templates', [
			'id' => $sceneTemplate->id,
			'name' => 'Новая сцена',
			'width' => 7,
		]);

		$this->assertDatabaseHas('scene_template_cells', [
			'scene_template_id' => $sceneTemplate->id,
			'x' => 2,
			'y' => 3,
			'terrain_type' => 'stone',
			'is_passable' => false,
			'blocks_vision' => true,
		]);

		$this->assertDatabaseHas('scene_objects', [
			'scene_template_id' => $sceneTemplate->id,
			'kind' => 'barrel',
			'x' => 1,
			'y' => 1,
		]);

		$this->assertDatabaseHas('scene_actor_placements', [
			'scene_template_id' => $sceneTemplate->id,
			'actor_id' => $actor->id,
			'x' => 3,
			'y' => 2,
		]);
	}

	/**
	 * Проверяет, что мастер может удалить authored-сцену из своей игры.
	 */
	public function test_game_master_can_delete_scene(): void
	{
		$gameMaster = User::query()->create([
			'name' => 'gm-scenes',
			'email' => 'gm-scenes@example.com',
			'can_access_gm' => true,
			'password' => 'secret-pass',
		]);

		$game = Game::query()->create([
			'title' => 'Моя игра',
			'gm_user_id' => $gameMaster->id,
			'status' => 'draft',
		]);

		$sceneTemplate = SceneTemplate::query()->create([
			'created_by' => $gameMaster->id,
			'name' => 'Удаляемая сцена',
			'width' => 6,
			'height' => 6,
			'status' => 'draft',
		]);

		$sceneState = GameSceneState::query()->create([
			'game_id' => $game->id,
			'scene_template_id' => $sceneTemplate->id,
			'status' => 'prepared',
			'version' => 1,
		]);

		$game->forceFill([
			'active_scene_state_id' => $sceneState->id,
		])->save();

		$csrfToken = $this->authenticate($gameMaster);

		$this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->deleteJson('/api/games/'.$game->id.'/scenes/'.$sceneState->id)
			->assertNoContent();

		$this->assertDatabaseMissing('game_scene_states', [
			'id' => $sceneState->id,
		]);

		$this->assertDatabaseMissing('scene_templates', [
			'id' => $sceneTemplate->id,
		]);

		$this->assertDatabaseHas('games', [
			'id' => $game->id,
			'active_scene_state_id' => null,
		]);
	}
}
