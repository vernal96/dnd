<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Data\Game\GameImageFileData;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Отдает служебные изображения кодового каталога предметов.
 */
final class ItemCatalogImageStorageService
{
	private const string DISK_NAME = 'game_images';
	private const string ITEM_IMAGES_DIRECTORY = 'support/items';

	/**
	 * Возвращает публичный URL картинки предмета.
	 */
	public function buildImageUrl(string $fileName): string
	{
		return '/api/item-images/' . basename($fileName);
	}

	/**
	 * Возвращает бинарный файл изображения предмета.
	 */
	public function findImage(string $fileName): ?GameImageFileData
	{
		$safeFileName = basename($fileName);
		$path = self::ITEM_IMAGES_DIRECTORY . '/' . $safeFileName;
		$disk = $this->getDisk();

		if (!$disk->exists($path)) {
			return null;
		}

		return new GameImageFileData(
			absolutePath: $disk->path($path),
			fileName: $safeFileName,
			mimeType: $this->resolveMimeType($disk, $path),
		);
	}

	/**
	 * Возвращает файловый диск изображений.
	 */
	private function getDisk(): Filesystem
	{
		return Storage::disk(self::DISK_NAME);
	}

	/**
	 * Возвращает MIME-тип файла или безопасное значение по умолчанию.
	 */
	private function resolveMimeType(Filesystem $disk, string $path): string
	{
		$mimeType = $disk->mimeType($path);

		return is_string($mimeType) && $mimeType !== ''
			? $mimeType
			: 'application/octet-stream';
	}
}
