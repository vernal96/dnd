<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\SceneCatalog\SceneObjectImageStorageService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает бинарные картинки authored-объектов сцены.
 */
final class SceneObjectImageController extends Controller
{
	/**
	 * Создает контроллер картинок authored-объектов.
	 */
	public function __construct(
		private readonly SceneObjectImageStorageService $sceneObjectImageStorageService,
	)
	{
	}

	/**
	 * Возвращает бинарное содержимое картинки authored-объекта.
	 */
	public function show(string $image): BinaryFileResponse|JsonResponse
	{
		$imageFile = $this->sceneObjectImageStorageService->findImage($image);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение объекта сцены не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
