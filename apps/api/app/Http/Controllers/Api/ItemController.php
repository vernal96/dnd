<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\ItemCatalog;
use App\Application\Catalog\ItemCatalogImageStorageService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPayloadResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Отдает API справочника предметов и снаряжения.
 */
final class ItemController extends Controller
{
	/**
	 * Создает контроллер справочника предметов.
	 */
	public function __construct(
		private readonly ItemCatalog $itemCatalog,
		private readonly ItemCatalogImageStorageService $itemCatalogImageStorageService,
	)
	{
	}

	/**
	 * Возвращает список активных предметов каталога.
	 */
	public function index(): JsonResponse
	{
		return ApiPayloadResource::json(array_map(
			fn (\App\Domain\Catalog\Item $item): array => $item->toArray(
				fn (string $fileName): string => $this->itemCatalogImageStorageService->buildImageUrl($fileName),
			),
			$this->itemCatalog->getActiveItems(),
		));
	}

	/**
	 * Возвращает один активный предмет по коду.
	 */
	public function show(string $item): JsonResponse
	{
		$itemDefinition = $this->itemCatalog->findActiveItemByCode($item);

		if ($itemDefinition === null) {
			return ApiPayloadResource::json([
				'message' => 'Предмет не найден.',
			], ResponseAlias::HTTP_NOT_FOUND);
		}

		return ApiPayloadResource::json($itemDefinition->toArray(
			fn (string $fileName): string => $this->itemCatalogImageStorageService->buildImageUrl($fileName),
		));
	}
}
