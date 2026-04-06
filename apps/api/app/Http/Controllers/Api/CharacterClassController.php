<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\CharacterClassCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Catalog\ActorCharacterClassResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает API справочника классов и подклассов персонажей.
 */
final class CharacterClassController extends Controller
{
	/**
	 * Создает контроллер справочника классов персонажей.
	 */
	public function __construct(
		private readonly CharacterClassCatalog $characterClassCatalog,
	)
	{
	}

	/**
	 * Возвращает список активных классов персонажей вместе с подклассами.
	 */
	public function index(): JsonResponse
	{
		return ActorCharacterClassResource::collection($this->characterClassCatalog->getPlayerSelectableClasses())
			->response()
			->setStatusCode(ResponseAlias::HTTP_OK);
	}

	/**
	 * Возвращает один активный класс персонажа вместе с его подклассами.
	 */
	public function show(string $characterClass): JsonResponse
	{
		$classDefinition = $this->characterClassCatalog->findPlayerSelectableClassByCode($characterClass);

		if ($classDefinition === null) {
			return ApiPayloadResource::json([
				'message' => 'Класс персонажа не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ActorCharacterClassResource::make($classDefinition)
			->response()
			->setStatusCode(ResponseAlias::HTTP_OK);
	}
}
