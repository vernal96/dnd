<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\RuntimeSceneManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Requests\Game\MoveRuntimeActorRequest;
use App\Http\Requests\Game\RuntimeDropItemRequest;
use App\Http\Requests\Game\RuntimePaintCellRequest;
use App\Http\Requests\Game\RuntimeSpawnActorRequest;
use App\Http\Resources\ApiPayloadResource;
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($sceneState));
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($activeScene));
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

		return ApiPayloadResource::json($actorInstance);
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($sceneState));
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($sceneState));
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($sceneState));
	}
}
