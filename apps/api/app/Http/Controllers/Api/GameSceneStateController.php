<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSceneState;
use Illuminate\Http\JsonResponse;

/**
 * Exposes runtime scene state snapshots for the API.
 */
class GameSceneStateController extends Controller
{
    /**
     * Возвращает пагинированный список состояний сцен вместе с игрой и исходным шаблоном.
     */
    public function index(): JsonResponse
    {
        $states = GameSceneState::query()
            ->with(['game:id,title,status', 'sceneTemplate:id,name,width,height,status'])
            ->latest('id')
            ->paginate(20);

        return response()->json($states);
    }

    /**
     * Возвращает одно состояние сцены вместе с акторами и encounter.
     */
    public function show(GameSceneState $gameSceneState): JsonResponse
    {
        $gameSceneState->load([
            'game:id,title,status',
            'sceneTemplate:id,name,width,height,status',
            'actorInstances',
            'encounters',
        ]);

        return response()->json($gameSceneState);
    }
}
