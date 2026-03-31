<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\RaceCatalog;
use App\Http\Controllers\Controller;
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
    ) {}

    /**
     * Возвращает список активных рас вместе с подрасами.
     */
    public function index(): JsonResponse
    {
        return response()->json(array_map(
            static fn ($race): array => $race->toArray(),
            $this->raceCatalog->getActiveRaces(),
        ));
    }

    /**
     * Возвращает одну активную расу вместе с ее подрасами.
     */
    public function show(string $race): JsonResponse
    {
        $raceDefinition = $this->raceCatalog->findActiveRaceByCode($race);

        if ($raceDefinition === null) {
            return response()->json([
                'message' => 'Раса не найдена.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($raceDefinition->toArray());
    }
}
