<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Player\PlayerCharacterManagementService;
use App\Data\Player\CreatePlayerCharacterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Player\CreatePlayerCharacterRequest;
use App\Http\Requests\Player\UpdatePlayerCharacterImageRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Player\PlayerCharacterPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Управляет persistent-персонажами текущего игрока.
 */
final class PlayerCharacterController extends Controller
{
	/**
	 * Создает контроллер персонажей игрока.
	 */
	public function __construct(
		private readonly PlayerCharacterManagementService $playerCharacterManagementService,
	)
	{
	}

	/**
	 * Возвращает список персонажей текущего игрока.
	 */
	public function index(Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		return PlayerCharacterPayloadResource::collection(
			$this->playerCharacterManagementService->getCharactersForPlayer($user),
		)->response();
	}

	/**
	 * Создает нового персонажа текущего игрока.
	 */
	public function store(CreatePlayerCharacterRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$character = $this->playerCharacterManagementService->createCharacter(
				CreatePlayerCharacterData::fromArray($request->validated()),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось создать персонажа.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		return PlayerCharacterPayloadResource::make($character)
			->response()
			->setStatusCode(ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Обновляет фото существующего персонажа текущего игрока.
	 */
	public function updateImage(int $character, UpdatePlayerCharacterImageRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$updatedCharacter = $this->playerCharacterManagementService->updateCharacterImage(
				$character,
				(string) $request->validated('image_path'),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось обновить фото персонажа.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($updatedCharacter === null) {
			return ApiPayloadResource::json([
				'message' => 'Персонаж не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return PlayerCharacterPayloadResource::make($updatedCharacter)->response();
	}
}
