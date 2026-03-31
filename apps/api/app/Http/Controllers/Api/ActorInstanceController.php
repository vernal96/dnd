<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActorInstance;
use Illuminate\Http\JsonResponse;

/**
 * Returns read-only actor instance data for API consumers.
 */
class ActorInstanceController extends Controller
{
    /**
     * Возвращает пагинированный список инстансов акторов с минимальным связанным контекстом.
     */
    public function index(): JsonResponse
    {
        $actors = ActorInstance::query()
            ->with([
                'game:id,title,status',
                'sceneState:id,game_id,scene_template_id,status',
                'playerCharacter:id,user_id,name,level,experience',
            ])
            ->latest('id')
            ->paginate(20);

        return response()->json($actors);
    }

    /**
     * Возвращает один инстанс актора вместе с игрой, владельцем и данными участия в encounter.
     */
    public function show(ActorInstance $actorInstance): JsonResponse
    {
        $actorInstance->load([
            'game:id,title,status',
            'sceneState:id,game_id,scene_template_id,status',
            'playerCharacter.user:id,name,email',
            'controller:id,name,email',
            'encounterParticipants.encounter:id,game_id,game_scene_state_id,status,round',
        ]);

        return response()->json($actorInstance);
    }
}
