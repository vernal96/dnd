<?php

declare(strict_types=1);

namespace App\Data\Player;

/**
 * Переносит данные создания персонажа игрока в application-слой.
 */
final readonly class CreatePlayerCharacterData
{
	/**
	 * Создает DTO нового персонажа игрока.
	 *
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $baseStats
	 */
	public function __construct(
		public string $name,
		public ?string $description,
		public string $raceCode,
		public ?string $subraceCode,
		public string $classCode,
		public array $baseStats,
		public ?string $imagePath,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{name:string,description?:?string,race:string,subrace?:?string,character_class:string,base_stats:array{str:int,dex:int,con:int,int:int,wis:int,cha:int},image_path?:?string} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			name: trim($payload['name']),
			description: isset($payload['description']) && is_string($payload['description']) && trim($payload['description']) !== ''
				? trim($payload['description'])
				: null,
			raceCode: $payload['race'],
			subraceCode: isset($payload['subrace']) && is_string($payload['subrace']) && trim($payload['subrace']) !== ''
				? trim($payload['subrace'])
				: null,
			classCode: $payload['character_class'],
			baseStats: $payload['base_stats'],
			imagePath: self::normalizeImagePath($payload['image_path'] ?? null),
		);
	}

	/**
	 * Нормализует путь изображения персонажа перед сохранением.
	 */
	private static function normalizeImagePath(?string $imagePath): ?string
	{
		if ($imagePath === null) {
			return null;
		}

		$normalizedPath = trim($imagePath, " \t\n\r\0\x0B/");

		return $normalizedPath === '' ? null : $normalizedPath;
	}
}
