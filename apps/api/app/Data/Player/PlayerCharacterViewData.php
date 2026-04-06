<?php

declare(strict_types=1);

namespace App\Data\Player;

/**
 * Представление персонажа игрока для outbound API.
 */
final readonly class PlayerCharacterViewData
{
	/**
	 * @param array<string, mixed>|null $baseStats
	 * @param array<string, mixed>|null $derivedStats
	 */
	public function __construct(
		public int $id,
		public int $userId,
		public string $name,
		public ?string $description,
		public ?string $race,
		public ?string $raceName,
		public ?string $subrace,
		public ?string $subraceName,
		public ?string $characterClass,
		public ?string $characterClassName,
		public int $level,
		public int $experience,
		public string $status,
		public ?array $baseStats,
		public ?array $derivedStats,
		public ?string $imagePath,
		public ?string $imageUrl,
		public ?int $activeGameId,
		public ?string $activeGameTitle,
		public bool $isAvailableForJoin,
		public ?string $createdAt,
		public ?string $updatedAt,
	)
	{
	}
}
