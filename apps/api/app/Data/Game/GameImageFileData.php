<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает файл изображения игры для отдачи в бинарном виде.
 */
final readonly class GameImageFileData
{
	/**
	 * Создает DTO файла изображения игры.
	 */
	public function __construct(
		public string $absolutePath,
		public string $fileName,
		public string $mimeType,
	)
	{
	}
}
