<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Player\PlayerCharacterImageStorageService;
use App\Data\Game\UploadGameImageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\UploadGameImageRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Http\Resources\Game\PlayerCharacterStoredImageResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Отдает API для хранения изображений персонажей игрока.
 */
final class PlayerCharacterImageController extends Controller
{
	/**
	 * Создает контроллер изображений персонажей игрока.
	 */
	public function __construct(
		private readonly PlayerCharacterImageStorageService $playerCharacterImageStorageService,
	)
	{
	}

	/**
	 * Загружает новое изображение персонажа игрока.
	 */
	public function store(UploadGameImageRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$image = $this->playerCharacterImageStorageService->storeImage(
				UploadGameImageData::fromArray([
					'file' => $request->getFile(),
				]),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось сохранить изображение персонажа.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		return (new PlayerCharacterStoredImageResource(
			$image,
			$this->playerCharacterImageStorageService->buildImagePath($image->fileName, $user),
		))->response()->setStatusCode(ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает бинарное содержимое изображения персонажа текущего игрока.
	 */
	public function show(string $image, Request $request): BinaryFileResponse|JsonResponse
	{
		$imageFile = $this->playerCharacterImageStorageService->findImageByFileName($image);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение персонажа не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
