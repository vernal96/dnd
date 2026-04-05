<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Data\Game\CreateSceneData;
use App\Data\Game\UpdateSceneData;
use App\Models\Game;
use App\Models\GameSceneState;
use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Управляет authored-сценами внутри конкретной игры мастера.
 */
final class SceneManagementService
{
	/**
	 * Создает новую сцену игры и ее runtime-состояние по умолчанию.
	 *
	 * @throws Throwable Если создание сцены завершилось технической ошибкой.
	 */
	public function createScene(int $gameId, CreateSceneData $data, User $user): ?GameSceneState
	{
		$game = $this->findOwnedGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		/** @var GameSceneState $sceneState */
		$sceneState = DB::transaction(function () use ($data, $game, $user): GameSceneState {
			/** @var SceneTemplate $sceneTemplate */
			$sceneTemplate = SceneTemplate::query()->create([
				'created_by' => $user->id,
				'name' => $data->name,
				'description' => $data->description,
				'width' => $data->width,
				'height' => $data->height,
				'status' => 'draft',
				'metadata' => $data->metadata,
			]);

			$sceneTemplate->cells()->createMany($this->buildCellRows(
				$sceneTemplate->id,
				$data->width,
				$data->height,
				[],
			));

			/** @var GameSceneState $createdSceneState */
			$createdSceneState = GameSceneState::query()->create([
				'game_id' => $game->id,
				'scene_template_id' => $sceneTemplate->id,
				'status' => 'prepared',
				'version' => 1,
				'grid_state' => null,
				'objects_state' => null,
				'visibility_state' => null,
				'effects_state' => null,
				'runtime_state' => null,
			]);

			if ($game->active_scene_state_id === null) {
				$game->forceFill([
					'active_scene_state_id' => $createdSceneState->id,
				])->save();
			}

			return $createdSceneState;
		});

		return $this->loadSceneState($sceneState);
	}

	/**
	 * Возвращает одну сцену игры текущего мастера вместе с сеткой клеток.
	 */
	public function findSceneForGameMaster(int $gameId, int $sceneStateId, User $user): ?GameSceneState
	{
		$sceneState = $this->findOwnedSceneState($gameId, $sceneStateId, $user);

		if ($sceneState === null) {
			return null;
		}

		return $this->loadSceneState($sceneState);
	}

	/**
	 * Сохраняет полное состояние authored-сцены.
	 *
	 * @throws Throwable Если сохранение сцены завершилось технической ошибкой.
	 */
	public function updateScene(int $gameId, int $sceneStateId, UpdateSceneData $data, User $user): ?GameSceneState
	{
		$sceneState = $this->findOwnedSceneState($gameId, $sceneStateId, $user);

		if ($sceneState === null) {
			return null;
		}

		DB::transaction(function () use ($data, $sceneState): void {
			$sceneTemplate = $sceneState->sceneTemplate()->firstOrFail();
			$sceneTemplate->fill([
				'name' => $data->name,
				'description' => $data->description,
				'width' => $data->width,
				'height' => $data->height,
				'metadata' => $data->metadata,
			]);
			$sceneTemplate->save();

			$sceneTemplate->cells()->delete();
			$sceneTemplate->cells()->createMany($this->buildCellRows(
				$sceneTemplate->id,
				$data->width,
				$data->height,
				$data->cells,
			));

			$sceneTemplate->objects()->delete();
			$sceneTemplate->objects()->createMany($this->buildObjectRows(
				$sceneTemplate->id,
				$data->objects,
			));

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});

		return $this->findSceneForGameMaster($gameId, $sceneStateId, $user);
	}

	/**
	 * Удаляет authored-сцену и связанное runtime-состояние из игры мастера.
	 *
	 * @throws Throwable Если удаление сцены завершилось технической ошибкой.
	 */
	public function deleteScene(int $gameId, int $sceneStateId, User $user): bool
	{
		$sceneState = $this->findOwnedSceneState($gameId, $sceneStateId, $user);

		if ($sceneState === null) {
			return false;
		}

		DB::transaction(function () use ($sceneState): void {
			$game = $sceneState->game()->firstOrFail();
			$sceneTemplate = $sceneState->sceneTemplate()->firstOrFail();

			if ($game->active_scene_state_id === $sceneState->id) {
				$game->forceFill([
					'active_scene_state_id' => null,
				])->save();
			}

			$sceneState->delete();
			$sceneTemplate->delete();
		});

		return true;
	}

	/**
	 * Возвращает игру, принадлежащую текущему мастеру.
	 */
	private function findOwnedGame(int $gameId, User $user): ?Game
	{
		return Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->first();
	}

	/**
	 * Возвращает runtime-состояние сцены, принадлежащее игре текущего мастера.
	 */
	private function findOwnedSceneState(int $gameId, int $sceneStateId, User $user): ?GameSceneState
	{
		return GameSceneState::query()
			->where('id', $sceneStateId)
			->where('game_id', $gameId)
			->whereHas('game', static function ($query) use ($user): void {
				$query->where('gm_user_id', $user->id);
			})
			->first();
	}

	/**
	 * Загружает все данные сцены, необходимые редактору.
	 */
	private function loadSceneState(GameSceneState $sceneState): GameSceneState
	{
		$sceneState->load([
			'game:id,title,gm_user_id,active_scene_state_id',
			'sceneTemplate:id,created_by,name,description,width,height,status,metadata,created_at,updated_at',
			'sceneTemplate.cells',
			'sceneTemplate.objects',
		]);

		return $sceneState;
	}

	/**
	 * Формирует полную прямоугольную сетку клеток для authored-сцены.
	 *
	 * @param array<int, array{x:int,y:int,terrainType:string,elevation:int,isPassable:bool,blocksVision:bool,props:?array}> $cells
	 * @return array<int, array<string, mixed>>
	 */
	private function buildCellRows(int $sceneTemplateId, int $width, int $height, array $cells): array
	{
		$cellMap = [];

		foreach ($cells as $cell) {
			$cellMap[$cell['x'].':'.$cell['y']] = $cell;
		}

		$rows = [];
		$timestamp = now();

		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$key = $x.':'.$y;
				$cell = $cellMap[$key] ?? null;

				$rows[] = [
					'scene_template_id' => $sceneTemplateId,
					'x' => $x,
					'y' => $y,
					'terrain_type' => is_array($cell) ? $cell['terrainType'] : 'grass',
					'elevation' => is_array($cell) ? $cell['elevation'] : 0,
					'is_passable' => is_array($cell) ? $cell['isPassable'] : true,
					'blocks_vision' => is_array($cell) ? $cell['blocksVision'] : false,
					'props' => is_array($cell) ? $cell['props'] : null,
					'created_at' => $timestamp,
					'updated_at' => $timestamp,
				];
			}
		}

		return $rows;
	}

	/**
	 * Формирует строки объектов для authored-сцены.
	 *
	 * @param array<int, array{kind:string,name:?string,x:int,y:int,width:int,height:int,isHidden:bool,isInteractive:bool,state:?array}> $objects
	 * @return array<int, array<string, mixed>>
	 */
	private function buildObjectRows(int $sceneTemplateId, array $objects): array
	{
		$rows = [];
		$timestamp = now();

		foreach ($objects as $object) {
			$rows[] = [
				'scene_template_id' => $sceneTemplateId,
				'kind' => $object['kind'],
				'name' => $object['name'],
				'x' => $object['x'],
				'y' => $object['y'],
				'width' => $object['width'],
				'height' => $object['height'],
				'is_hidden' => $object['isHidden'],
				'is_interactive' => $object['isInteractive'],
				'state' => $object['state'],
				'trigger_rules' => null,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			];
		}

		return $rows;
	}
}
