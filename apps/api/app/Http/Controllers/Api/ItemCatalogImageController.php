<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\ItemCatalogImageStorageService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает бинарные картинки кодового каталога предметов.
 */
final class ItemCatalogImageController extends Controller
{
	/**
	 * Создает контроллер картинок предметов.
	 */
	public function __construct(
		private readonly ItemCatalogImageStorageService $itemCatalogImageStorageService,
	)
	{
	}

	/**
	 * Возвращает бинарное содержимое картинки предмета.
	 */
	public function show(string $image): BinaryFileResponse|JsonResponse
	{
		$imageFile = $this->itemCatalogImageStorageService->findImage($image);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение предмета не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
