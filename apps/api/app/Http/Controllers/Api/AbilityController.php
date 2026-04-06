<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\AbilityCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ActorAbilityResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
		return ActorAbilityResource::collection($this->abilityCatalog->getAbilities())
			->response()
			->setStatusCode(ResponseAlias::HTTP_OK);
	}
}
