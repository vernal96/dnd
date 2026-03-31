<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\GameImageStorageService;
use App\Data\Game\UploadGameImageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\UploadGameImageRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает API для хранения изображений игровых столов.
 */
final class GameImageController extends Controller
{
	/**
	 * Создает контроллер игровых изображений.
	 */
	public function __construct(
		private readonly GameImageStorageService $gameImageStorageService,
	)
	{
	}

	/**
	 * Возвращает список изображений указанной игры.
	 */
	public function index(int $game, Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$images = $this->gameImageStorageService->getImages($game, $user);

		if ($images === null) {
			return ApiPayloadResource::json([
				'message' => 'Игра не найдена.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::collectionJson($images);
	}

	/**
	 * Загружает изображение в каталог игры текущего мастера.
	 */
	public function store(int $game, UploadGameImageRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$image = $this->gameImageStorageService->storeImage(
				$game,
				UploadGameImageData::fromArray([
					'file' => $request->getFile(),
				]),
				$user,
			);
		} catch (RuntimeException $exception) {
			return ApiPayloadResource::json([
				'message' => $exception->getMessage(),
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($image, ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает бинарное содержимое изображения игры.
	 */
	public function show(int $game, string $image, Request $request): BinaryFileResponse|JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$imageFile = $this->gameImageStorageService->findImage($game, $image, $user);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
