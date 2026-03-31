<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SceneTemplate;
use Illuminate\Http\JsonResponse;

class SceneTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = SceneTemplate::query()
            ->withCount(['cells', 'objects', 'sceneStates'])
            ->latest('id')
            ->paginate(20);

        return response()->json($templates);
    }

    public function show(SceneTemplate $sceneTemplate): JsonResponse
    {
        $sceneTemplate->load([
            'author:id,name,email',
            'cells',
            'objects',
        ]);

        return response()->json($sceneTemplate);
    }
}
