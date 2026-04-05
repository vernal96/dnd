<?php

declare(strict_types=1);

namespace App\Application\SceneCatalog;

use App\Data\Game\GameImageFileData;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Отдает служебные изображения authored-объектов сцены.
 */
final class SceneObjectImageStorageService
{
	private const string DISK_NAME = 'game_images';
	private const string OBJECT_IMAGES_DIRECTORY = 'support/objects';

	/**
	 * Возвращает публичный URL картинки authored-объекта.
	 */
	public function buildImageUrl(string $fileName): string
	{
		return '/api/scene-catalog/object-images/' . basename($fileName);
	}

	/**
	 * Возвращает бинарный файл изображения authored-объекта.
	 */
	public function findImage(string $fileName): ?GameImageFileData
	{
		$safeFileName = basename($fileName);
		$path = self::OBJECT_IMAGES_DIRECTORY . '/' . $safeFileName;
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
