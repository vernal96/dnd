<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\GameInvitationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\AcceptGameInvitationRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

/**
 * Отдает API приглашений в игровые столы для игрока.
 */
final class GameInvitationController extends Controller
{
	/**
	 * Создает контроллер API приглашений.
	 */
	public function __construct(
		private readonly GameInvitationService $gameInvitationService,
	)
	{
	}

	/**
	 * Возвращает список непринятых приглашений текущего игрока.
	 */
	public function index(Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		return ApiPayloadResource::json($this->gameInvitationService->getInvitationsForPlayer($user));
	}

	/**
	 * Возвращает персонажей игрока, которыми можно принять выбранное приглашение.
	 */
	public function availableCharacters(string $token, Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$characters = $this->gameInvitationService->getAvailableCharactersForInvitation($token, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось загрузить доступных персонажей.',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($characters === null) {
			return ApiPayloadResource::json([
				'message' => 'Приглашение не найдено.',
			], Response::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::collectionJson($characters);
	}

	/**
	 * Принимает приглашение и добавляет игрока в игру.
	 */
	public function accept(string $token, AcceptGameInvitationRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$invitation = $this->gameInvitationService->acceptInvitation(
				$token,
				(int) $request->validated('character_id'),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось принять приглашение.',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($invitation === null) {
			return ApiPayloadResource::json([
				'message' => 'Приглашение не найдено.',
			], Response::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($invitation);
	}

	/**
	 * Отклоняет приглашение игрока без вступления в игру.
	 */
	public function decline(string $token, Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$invitation = $this->gameInvitationService->declineInvitation($token, $user);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось отклонить приглашение.',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($invitation === null) {
			return ApiPayloadResource::json([
				'message' => 'Приглашение не найдено.',
			], Response::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($invitation);
	}
}
