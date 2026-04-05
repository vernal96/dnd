<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use App\Support\SceneCatalog\SceneObjectCatalog;
use App\Support\SceneCatalog\SceneSurfaceCatalog;
use Illuminate\Http\JsonResponse;

/**
 * Отдает серверный каталог поверхностей и объектов редактора сцены.
 */
final class SceneCatalogController extends Controller
{
	/**
	 * Возвращает доступные поверхности редактора.
	 */
	public function surfaces(): JsonResponse
	{
		return ApiPayloadResource::json(SceneSurfaceCatalog::all());
	}

	/**
	 * Возвращает доступные объекты редактора.
	 */
	public function objects(): JsonResponse
	{
		return ApiPayloadResource::json(SceneObjectCatalog::all());
	}
}
