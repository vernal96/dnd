<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

/**
 * Exposes game read models for the API.
 */
class GameController extends Controller
{
    /**
     * Возвращает пагинированный список игр вместе с мастером и активной сценой.
     */
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->with(['gm:id,name,email', 'activeSceneState:id,game_id,scene_template_id,status,version'])
            ->latest('id')
            ->paginate(20);

        return response()->json($games);
    }

    /**
     * Возвращает одну игру вместе с участниками и шаблоном активной сцены.
     */
    public function show(Game $game): JsonResponse
    {
        $game->load([
            'gm:id,name,email',
            'members.user:id,name,email',
            'activeSceneState.sceneTemplate:id,name,width,height,status',
        ]);

        return response()->json($game);
    }
}
