<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\ActorManagementService;
use App\Data\Game\CreateActorData;
use App\Data\Game\UpdateActorData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\CreateActorRequest;
use App\Http\Requests\Game\ManageActorRequest;
use App\Http\Requests\Game\UpdateActorRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Управляет библиотекой persistent-акторов текущего мастера.
 */
final class ActorController extends Controller
{
	/**
	 * Создает контроллер persistent-акторов.
	 */
	public function __construct(
		private readonly ActorManagementService $actorManagementService,
	)
	{
	}

	/**
	 * Возвращает список persistent-акторов текущего мастера.
	 */
	public function index(ManageActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$actors = $this->actorManagementService->getActorsForGameMaster($user);

		return ApiPayloadResource::json($actors);
	}

	/**
	 * Создает нового persistent-актора в библиотеке текущего мастера.
	 */
	public function store(CreateActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$actor = $this->actorManagementService->createActor(
				CreateActorData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось создать актора.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		return ApiPayloadResource::json($actor, ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает одного persistent-актора текущего мастера.
	 */
	public function show(int $actor, ManageActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$actorModel = $this->actorManagementService->findActorForGameMaster($actor, $user);

		if ($actorModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Актор не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($actorModel);
	}

	/**
	 * Полностью обновляет persistent-актора текущего мастера.
	 */
	public function update(int $actor, UpdateActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$actorModel = $this->actorManagementService->updateActor(
				$actor,
				UpdateActorData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось сохранить актора.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($actorModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Актор не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($actorModel);
	}

	/**
	 * Удаляет persistent-актора из библиотеки текущего мастера.
	 */
	public function destroy(int $actor, ManageActorRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$isDeleted = $this->actorManagementService->deleteActor($actor, $user);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось удалить актора.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!$isDeleted) {
			return ApiPayloadResource::json([
				'message' => 'Актор не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json([], ResponseAlias::HTTP_NO_CONTENT);
	}
}
