<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\RuntimeSceneManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Requests\Game\MoveRuntimeActorRequest;
use App\Http\Requests\Game\RuntimeActorActionRequest;
use App\Http\Requests\Game\RuntimeActorEquipmentRequest;
use App\Http\Requests\Game\RuntimeDropItemRequest;
use App\Http\Requests\Game\RuntimePaintCellRequest;
use App\Http\Requests\Game\RuntimeSpawnActorRequest;
use App\Http\Requests\Game\StartEncounterRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Game\ActorInstanceResource;
use App\Http\Resources\Game\RuntimeSceneViewResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Отдает API runtime-сцены для кабинета мастера.
 */
final class GmRuntimeSceneController extends Controller
{
	/**
	 * Создает runtime-контроллер сцены.
	 */
	public function __construct(
		private readonly RuntimeSceneManagementService $runtimeSceneManagementService,
	)
	{
	}

	/**
	 * Возвращает активную runtime-сцену игры текущего мастера.
	 */
	public function showActive(int $game, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$sceneState = $this->runtimeSceneManagementService->findActiveSceneForGameMaster($game, $user);

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная runtime-сцена не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Активирует выбранную authored-сцену как runtime-сцену игры мастера.
	 */
	public function activate(int $game, int $sceneState, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$activeScene = $this->runtimeSceneManagementService->activateScene($game, $sceneState, $user);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось запустить сцену.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($activeScene === null) {
			return ApiPayloadResource::json([
				'message' => 'Сцена или игра не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($activeScene))
			->response();
	}

	/**
	 * Перемещает runtime-актора по активной сцене игры текущего мастера.
	 */
	public function moveActor(int $game, int $actor, MoveRuntimeActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$actorInstance = $this->runtimeSceneManagementService->moveActor(
				$game,
				$actor,
				(int) $request->validated('x'),
				(int) $request->validated('y'),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось переместить персонажа по сцене.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($actorInstance === null) {
			return ApiPayloadResource::json([
				'message' => 'Runtime-актор или игра не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ActorInstanceResource::make($actorInstance)->response();
	}

	/**
	 * Выполняет runtime-действие актора от имени мастера.
	 */
	public function performAction(int $game, int $actor, RuntimeActorActionRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->performRuntimeAction(
				gameId: $game,
				actorInstanceId: $actor,
				action: (string) $request->validated('action'),
				targetActorId: (int) $request->validated('target_actor_id'),
				equipmentSlot: is_string($request->validated('equipment_slot')) ? (string) $request->validated('equipment_slot') : null,
				itemCode: is_string($request->validated('item_code')) ? (string) $request->validated('item_code') : null,
				user: $user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось выполнить действие актора.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Runtime-актор или игра не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Изменяет экипировку runtime-актора от имени мастера.
	 */
	public function equipActor(int $game, int $actor, RuntimeActorEquipmentRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->equipRuntimeActor(
				gameId: $game,
				actorInstanceId: $actor,
				slot: (string) $request->validated('slot'),
				itemCode: is_string($request->validated('item_code')) ? (string) $request->validated('item_code') : null,
				user: $user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось изменить экипировку актора.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Runtime-актор или игра не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Размещает нового runtime-актора на активной сцене.
	 */
	public function spawnActor(int $game, RuntimeSpawnActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->spawnActor(
				$game,
				(string) $request->validated('source_type'),
				(int) $request->validated('source_id'),
				(int) $request->validated('x'),
				(int) $request->validated('y'),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось добавить персонажа на активную сцену.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Изменяет поверхность клетки на активной runtime-сцене.
	 */
	public function paintCell(int $game, RuntimePaintCellRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->paintCell(
				$game,
				(int) $request->validated('x'),
				(int) $request->validated('y'),
				(string) $request->validated('terrain_type'),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось изменить поверхность runtime-сцены.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Размещает предмет на активной runtime-сцене.
	 */
	public function dropItem(int $game, RuntimeDropItemRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->dropItem(
				$game,
				(string) $request->validated('item_code'),
				(int) $request->validated('x'),
				(int) $request->validated('y'),
				(int) ($request->validated('quantity') ?? 1),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось разместить предмет на сцене.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Запускает encounter на активной runtime-сцене.
	 */
	public function startEncounter(int $game, StartEncounterRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->startEncounter(
				$game,
				array_values(array_map('intval', $request->validated('actor_ids', []))),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось запустить сражение.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Расходует основное действие текущего участника encounter.
	 */
	public function useAction(int $game, int $actor, ListGamesRequest $request): JsonResponse
	{
		return $this->useEncounterAction($game, $actor, 'action', $request);
	}

	/**
	 * Расходует дополнительное действие текущего участника encounter.
	 */
	public function useBonusAction(int $game, int $actor, ListGamesRequest $request): JsonResponse
	{
		return $this->useEncounterAction($game, $actor, 'bonus-action', $request);
	}

	/**
	 * Переводит encounter на следующий ход.
	 */
	public function nextTurn(int $game, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->advanceEncounterTurn($game, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось завершить текущий ход.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Завершает активное сражение на runtime-сцене.
	 */
	public function endEncounter(int $game, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->endEncounter($game, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось завершить сражение.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Выполняет расход указанного боевого ресурса текущего участника encounter.
	 */
	private function useEncounterAction(int $game, int $actor, string $actionType, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->useEncounterAction($game, $actor, $actionType, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось обновить состояние боевого хода.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная сцена игры не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}
}
