<?php

declare(strict_types=1);

namespace App\Data\Game;

use Illuminate\Http\UploadedFile;

/**
 * Переносит загруженный файл изображения игры в application-слой.
 */
final readonly class UploadGameImageData
{
	/**
	 * Создает DTO загружаемого изображения игры.
	 */
	public function __construct(
		public UploadedFile $file,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload запроса.
	 *
	 * @param array{file:UploadedFile} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			file: $payload['file'],
		);
	}
}
