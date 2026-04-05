<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\RuntimeSceneManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Requests\Game\MoveRuntimeActorRequest;
use App\Http\Resources\ApiPayloadResource;
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

		return ApiPayloadResource::json($this->runtimeSceneManagementService->toScenePayload($sceneState));
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

		return ApiPayloadResource::json($actorInstance);
	}
}
