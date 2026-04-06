<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\StoredGameImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует DTO сохраненного изображения в JSON.
 *
 * @mixin StoredGameImageData
 */
final class StoredGameImageResource extends JsonResource
{
	/**
	 * @return array{fileName: string, originalName: string, mimeType: string, fileSize: int, downloadUrl: string}
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
		];
	}
}
