<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\CharacterClassCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
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
		return ApiPayloadResource::collectionJson($this->characterClassCatalog->getActiveClasses());
	}

	/**
	 * Возвращает один активный класс персонажа вместе с его подклассами.
	 */
	public function show(string $characterClass): JsonResponse
	{
		$classDefinition = $this->characterClassCatalog->findActiveClassByCode($characterClass);

		if ($classDefinition === null) {
			return ApiPayloadResource::json([
				'message' => 'Класс персонажа не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($classDefinition);
	}
}
