<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\ActorImageStorageService;
use App\Data\Game\UploadGameImageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\UploadGameImageRequest;
use App\Http\Resources\ApiPayloadResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Отдает API для хранения изображений библиотеки NPC мастера.
 */
final class ActorImageController extends Controller
{
	/**
	 * Создает контроллер изображений NPC.
	 */
	public function __construct(
		private readonly ActorImageStorageService $actorImageStorageService,
	)
	{
	}

	/**
	 * Возвращает список изображений библиотеки NPC текущего мастера.
	 */
	public function index(Request $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		return ApiPayloadResource::collectionJson($this->actorImageStorageService->getImages($user));
	}

	/**
	 * Загружает новое изображение NPC в библиотеку текущего мастера.
	 */
	public function store(UploadGameImageRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');

		try {
			$image = $this->actorImageStorageService->storeImage(
				UploadGameImageData::fromArray([
					'file' => $request->getFile(),
				]),
				$user,
			);
		} catch (Throwable $throwable) {
			report($throwable);

			return ApiPayloadResource::json([
				'message' => 'Не удалось сохранить изображение NPC.',
			], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
		}

		return ApiPayloadResource::json($image, ResponseAlias::HTTP_CREATED);
	}

	/**
	 * Возвращает бинарное содержимое изображения NPC текущего мастера.
	 */
	public function show(string $image, Request $request): BinaryFileResponse|JsonResponse
	{
		/** @var User $user */
		$user = $request->user('web');
		$imageFile = $this->actorImageStorageService->findImage($image, $user);

		if ($imageFile === null) {
			return ApiPayloadResource::json([
				'message' => 'Изображение NPC не найдено.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return response()->file($imageFile->absolutePath, [
			'Content-Type' => $imageFile->mimeType,
			'Content-Disposition' => 'inline; filename="' . $imageFile->fileName . '"',
		]);
	}
}
