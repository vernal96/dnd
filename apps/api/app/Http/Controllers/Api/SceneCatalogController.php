<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\SceneCatalog\SceneObjectImageStorageService;
use App\Application\SceneCatalog\SceneSurfaceImageStorageService;
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
	 * Создает контроллер каталога сцены.
	 */
	public function __construct(
		private readonly SceneObjectImageStorageService $sceneObjectImageStorageService,
		private readonly SceneSurfaceImageStorageService $sceneSurfaceImageStorageService,
	)
	{
	}

	/**
	 * Возвращает доступные поверхности редактора.
	 */
	public function surfaces(): JsonResponse
	{
		return ApiPayloadResource::json(SceneSurfaceCatalog::all(
			fn (string $fileName): string => $this->sceneSurfaceImageStorageService->buildImageUrl($fileName),
		));
	}

	/**
	 * Возвращает доступные объекты редактора.
	 */
	public function objects(): JsonResponse
	{
		return ApiPayloadResource::json(SceneObjectCatalog::all(
			fn (string $fileName): string => $this->sceneObjectImageStorageService->buildImageUrl($fileName),
		));
	}
}
