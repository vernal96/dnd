<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\RaceCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Catalog\ActorRaceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Отдает API справочника рас и подрас.
 */
final class RaceController extends Controller
{
	/**
	 * Создает контроллер справочника рас.
	 */
	public function __construct(
		private readonly RaceCatalog $raceCatalog,
	)
	{
	}

	/**
	 * Возвращает список активных рас вместе с подрасами.
	 */
	public function index(): JsonResponse
	{
		return ActorRaceResource::collection($this->raceCatalog->getPlayerSelectableRaces())
			->response()
			->setStatusCode(Response::HTTP_OK);
	}

	/**
	 * Возвращает одну активную расу вместе с ее подрасами.
	 */
	public function show(string $race): JsonResponse
	{
		$raceDefinition = $this->raceCatalog->findPlayerSelectableRaceByCode($race);

		if ($raceDefinition === null) {
			return ApiPayloadResource::json([
				'message' => 'Раса не найдена.',
			], Response::HTTP_NOT_FOUND);
		}

		return ActorRaceResource::make($raceDefinition)
			->response()
			->setStatusCode(Response::HTTP_OK);
	}
}
