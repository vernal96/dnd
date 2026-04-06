<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\StoredGameImageData;
use Illuminate\Http\Request;

/**
 * Преобразует сохраненное изображение персонажа игрока в JSON с путем хранения.
 */
final class PlayerCharacterStoredImageResource extends StoredGameImageResource
{
	/**
	 * @param string $storagePath
	 */
	public function __construct($resource, private readonly string $storagePath)
	{
		parent::__construct($resource);
	}

	/**
	 * @return array{fileName: string, originalName: string, mimeType: string, fileSize: int, downloadUrl: string, storagePath: string}
	 */
	public function toArray(Request $request): array
	{
		/** @var StoredGameImageData $image */
		$image = $this->resource;

		return [
			'fileName' => $image->fileName,
			'originalName' => $image->originalName,
			'mimeType' => $image->mimeType,
			'fileSize' => $image->fileSize,
			'downloadUrl' => $image->downloadUrl,
			'storagePath' => $this->storagePath,
		];
	}
}
