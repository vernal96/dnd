<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\SceneManagementService;
use App\Data\Game\CreateSceneData;
use App\Data\Game\UpdateSceneData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\CreateSceneRequest;
use App\Http\Requests\Game\UpdateSceneRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Game\GameSceneStateResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Управляет authored-сценами внутри игры текущего мастера.
 */
final class GameSceneController extends Controller
{
	/**
	 * Создает контроллер authored-сцен.
	 */
	public function __construct(
		private readonly SceneManagementService $sceneManagementService,
	)
	{
	}

	/**
	 * Создает новую сцену внутри игры текущего мастера.
	 */
	public function store(int $game, CreateSceneRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$scene = $this->sceneManagementService->createScene(
				$game,
				CreateSceneData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось создать сцену.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($scene === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameSceneStateResource::make($scene)
			->response()
			->setStatusCode(ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает одну authored-сцену игры текущего мастера.
	 */
	public function show(int $game, int $scene, CreateSceneRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$sceneModel = $this->sceneManagementService->findSceneForGameMaster($game, $scene, $user);

		if ($sceneModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Сцена не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameSceneStateResource::make($sceneModel)->response();
	}

	/**
	 * Сохраняет authored-сцену игры текущего мастера.
	 */
	public function update(int $game, int $scene, UpdateSceneRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$sceneModel = $this->sceneManagementService->updateScene(
				$game,
				$scene,
				UpdateSceneData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось сохранить сцену.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($sceneModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Сцена не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameSceneStateResource::make($sceneModel)->response();
	}

	/**
	 * Удаляет authored-сцену из игры текущего мастера.
	 */
	public function destroy(int $game, int $scene, CreateSceneRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$isDeleted = $this->sceneManagementService->deleteScene($game, $scene, $user);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось удалить сцену.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!$isDeleted) {
			return ApiPayloadResource::json([
				'message' => 'Сцена не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json([], ResponseAlias::HTTP_NO_CONTENT);
	}
}
