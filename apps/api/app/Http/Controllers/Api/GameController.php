<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\GameInvitationService;
use App\Application\Game\GameManagementService;
use App\Data\Game\CreateGameData;
use App\Data\Game\GameListFiltersData;
use App\Data\Game\InviteGameMemberData;
use App\Data\Game\UpdateGameStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\CreateGameRequest;
use App\Http\Requests\Game\InviteGameMemberRequest;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Requests\Game\UpdateGameStatusRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Game\GameResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Отдает API кабинета мастера для чтения и создания игр.
 */
final class GameController extends Controller
{
	/**
	 * Создает контроллер API игр.
	 */
	public function __construct(
		private readonly GameManagementService $gameManagementService,
		private readonly GameInvitationService $gameInvitationService,
	)
	{
	}

	/**
	 * Возвращает список игр текущего мастера вместе с мастером и активной сценой.
	 */
	public function index(ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$games = $this->gameManagementService->getGamesForGameMaster(
			$user,
			GameListFiltersData::fromArray($request->validated()),
		);

		return GameResource::collection($games)->response();
	}

	/**
	 * Создает новую игру для текущего мастера.
	 */
	public function store(CreateGameRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$game = $this->gameManagementService->createGame(
				CreateGameData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось создать новую игру.',
			], 500);
		}

		return GameResource::make($game)
			->response()
			->setStatusCode(ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает одну игру вместе с участниками и шаблоном активной сцены.
	 */
	public function show(int $game, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$gameModel = $this->gameManagementService->findGameForGameMaster($game, $user);

		if ($gameModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameResource::make($gameModel)->response();
	}

	/**
	 * Обновляет статус игры текущего мастера.
	 */
	public function updateStatus(int $game, UpdateGameStatusRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$gameModel = $this->gameManagementService->updateGameStatus(
				$game,
				UpdateGameStatusData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось обновить статус игры.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($gameModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameResource::make($gameModel)->response();
	}

	/**
	 * Создает приглашение участнику в игру текущего мастера.
	 */
	public function inviteMember(int $game, InviteGameMemberRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$gameModel = $this->gameInvitationService->inviteMember(
				$game,
				InviteGameMemberData::fromArray($request->validated()),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось отправить приглашение в игру.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($gameModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameResource::make($gameModel)->response();
	}

	/**
	 * Удаляет участника из игры текущего мастера.
	 */
	public function removeMember(int $game, int $member, ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$gameModel = $this->gameManagementService->removeMember($game, $member, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось удалить участника из игры.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($gameModel === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра или участник не найдены.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return GameResource::make($gameModel)->response();
	}
}
