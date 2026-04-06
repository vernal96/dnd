<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\RuntimeSceneManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Requests\Game\MoveRuntimeActorRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Game\ActorInstanceResource;
use App\Http\Resources\Game\RuntimeSceneViewResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Отдает runtime-сцену игры для кабинета игрока.
 */
final class PlayerRuntimeSceneController extends Controller
{
	/**
	 * Создает контроллер runtime-сцены игрока.
	 */
	public function __construct(
		private readonly RuntimeSceneManagementService $runtimeSceneManagementService,
	)
	{
	}

	/**
	 * Возвращает активную runtime-сцену игры, доступную текущему игроку.
	 */
	public function showActive(int $game, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$sceneState = $this->runtimeSceneManagementService->findActiveSceneForPlayer($game, $user);

		if ($sceneState === null) {
			return ApiPayloadResource::json([
				'message' => 'Активная runtime-сцена для игрока не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Перемещает героя, которым управляет текущий игрок, по активной сцене.
	 */
	public function moveActor(int $game, int $actor, MoveRuntimeActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$actorInstance = $this->runtimeSceneManagementService->moveActorForPlayer(
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
				'message' => 'Не удалось переместить героя по активной сцене.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($actorInstance === null) {
			return ApiPayloadResource::json([
				'message' => 'Герой игрока или активная сцена не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ActorInstanceResource::make($actorInstance)->response();
	}

	/**
	 * Расходует основное действие текущего героя игрока в encounter.
	 */
	public function useAction(int $game, int $actor, ListGamesRequest $request): JsonResponse
	{
		return $this->useEncounterAction($game, $actor, 'action', $request);
	}

	/**
	 * Расходует дополнительное действие текущего героя игрока в encounter.
	 */
	public function useBonusAction(int $game, int $actor, ListGamesRequest $request): JsonResponse
	{
		return $this->useEncounterAction($game, $actor, 'bonus-action', $request);
	}

	/**
	 * Завершает ход текущего героя игрока.
	 */
	public function nextTurn(int $game, int $actor, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->advanceEncounterTurnForPlayer($game, $actor, $user);
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
				'message' => 'Герой игрока или активная сцена не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}

	/**
	 * Обновляет состояние боевого ресурса игрока в encounter.
	 */
	private function useEncounterAction(int $game, int $actor, string $actionType, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneState = $this->runtimeSceneManagementService->useEncounterActionForPlayer($game, $actor, $actionType, $user);
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
				'message' => 'Герой игрока или активная сцена не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return RuntimeSceneViewResource::make($this->runtimeSceneManagementService->buildSceneView($sceneState))
			->response();
	}
}
