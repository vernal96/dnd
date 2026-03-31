<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSceneState;
use Illuminate\Http\JsonResponse;

class GameSceneStateController extends Controller
{
    public function index(): JsonResponse
    {
        $states = GameSceneState::query()
            ->with(['game:id,title,status', 'sceneTemplate:id,name,width,height,status'])
            ->latest('id')
            ->paginate(20);

        return response()->json($states);
    }

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
