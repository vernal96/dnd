<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает сохраненное изображение игры для ответа API.
 */
final readonly class StoredGameImageData
{
	/**
	 * Создает DTO сохраненного изображения.
	 */
	public function __construct(
		public string $fileName,
		public string $originalName,
		public string $mimeType,
		public int    $fileSize,
		public string $downloadUrl,
	)
	{
	}

	/**
	 * Преобразует DTO в массив для JSON-ответа.
	 *
	 * @return array{fileName:string,originalName:string,mimeType:string,fileSize:int,downloadUrl:string}
	 */
	public function toArray(): array
	{
		return [
			'fileName' => $this->fileName,
			'originalName' => $this->originalName,
			'mimeType' => $this->mimeType,
			'fileSize' => $this->fileSize,
			'downloadUrl' => $this->downloadUrl,
		];
	}
}
