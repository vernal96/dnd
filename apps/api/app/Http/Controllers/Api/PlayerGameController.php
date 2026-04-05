<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Player\PlayerGameParticipationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ListGamesRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Отдает API активных игр игрока из его кабинета.
 */
final class PlayerGameController extends Controller
{
	/**
	 * Создает контроллер активных игр игрока.
	 */
	public function __construct(
		private readonly PlayerGameParticipationService $playerGameParticipationService,
	)
	{
	}

	/**
	 * Возвращает активные игры, где участвуют персонажи текущего игрока.
	 */
	public function indexActive(ListGamesRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		return ApiPayloadResource::json(
			$this->playerGameParticipationService->getActiveGamesForPlayer($user),
		);
	}
}
