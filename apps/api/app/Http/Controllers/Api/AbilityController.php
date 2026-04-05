<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\AbilityCatalog;
use App\Domain\Catalog\Ability;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use Illuminate\Http\JsonResponse;

/**
 * Отдает API справочника базовых характеристик персонажа.
 */
final class AbilityController extends Controller
{
	/**
	 * Создает контроллер справочника характеристик.
	 */
	public function __construct(
		private readonly AbilityCatalog $abilityCatalog,
	)
	{
	}

	/**
	 * Возвращает список базовых характеристик персонажа.
	 */
	public function index(): JsonResponse
	{
		return ApiPayloadResource::collectionJson(array_map(
			static fn (Ability $ability): array => [
				'code' => $ability->getCode(),
				'name' => $ability->getName(),
				'description' => $ability->getDescription(),
				'defaultValue' => 1,
			],
			$this->abilityCatalog->getAbilities(),
		));
	}
}
