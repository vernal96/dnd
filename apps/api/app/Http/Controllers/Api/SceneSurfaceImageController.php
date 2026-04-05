<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\SceneCatalog\SceneSurfaceImageStorageService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает бинарные картинки поверхностей сцены.
 */
final class SceneSurfaceImageController extends Controller
{
	/**
	 * Создает контроллер картинок поверхностей.
	 */
	public function __construct(
		private readonly SceneSurfaceImageStorageService $sceneSurfaceImageStorageService,
	)
	{
	}

	/**
	 * Возвращает бинарное содержимое картинки поверхности.
	 */
	public function show(string $image): BinaryFileResponse|JsonResponse
	{
		$imageFile = $this->sceneSurfaceImageStorageService->findImage($image);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение поверхности сцены не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
