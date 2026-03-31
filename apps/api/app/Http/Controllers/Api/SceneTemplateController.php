<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use App\Models\SceneTemplate;
use Illuminate\Http\JsonResponse;

/**
 * Exposes authored scene templates for the API.
 */
final class SceneTemplateController extends Controller
{
	/**
	 * Возвращает пагинированный список шаблонов сцен со структурными счетчиками.
	 */
	public function index(): JsonResponse
	{
		$templates = SceneTemplate::query()
			->withCount(['cells', 'objects', 'sceneStates'])
			->latest('id')
			->paginate(20);

		return ApiPayloadResource::collectionJson($templates);
	}

	/**
	 * Возвращает один шаблон сцены вместе с автором, клетками и объектами.
	 */
	public function show(SceneTemplate $sceneTemplate): JsonResponse
	{
		$sceneTemplate->load([
			'author:id,name,email',
			'cells',
			'objects',
		]);

		return ApiPayloadResource::json($sceneTemplate);
	}
}
