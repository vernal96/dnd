<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает данные для полного обновления persistent-актора игры.
 */
final readonly class UpdateActorData
{
	/**
	 * Создает DTO обновления актора.
	 *
	 * @param array<string, mixed>|null $stats
	 * @param list<ActorInventoryItemData> $inventory
	 * @param array<string, mixed>|null $meta
	 */
	public function __construct(
		public string $kind,
		public string $name,
		public ?string $description,
		public ?string $race,
		public ?string $characterClass,
		public int $level,
		public int $movementSpeed,
		public ?int $baseHealth,
		public ?int $healthCurrent,
		public ?int $healthMax,
		public string $luck,
		public ?array $stats,
		public array $inventory,
		public ?string $imagePath,
		public ?array $meta,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{kind:string,name:string,description?:?string,race?:?string,character_class?:?string,level:int,movement_speed:int,base_health?:?int,health_current?:?int,health_max?:?int,luck?:string,stats?:array<string, mixed>|null,inventory?:array<int, array<string, mixed>>,image_path?:?string,meta?:array<string, mixed>|null} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			kind: $payload['kind'],
			name: $payload['name'],
			description: $payload['description'] ?? null,
			race: $payload['race'] ?? null,
			characterClass: $payload['character_class'] ?? null,
			level: $payload['level'],
			movementSpeed: $payload['movement_speed'],
			baseHealth: $payload['base_health'] ?? null,
			healthCurrent: $payload['health_current'] ?? null,
			healthMax: $payload['health_max'] ?? null,
			luck: $payload['luck'] ?? 'normal',
			stats: $payload['stats'] ?? null,
			inventory: array_map(
				static fn (array $item): ActorInventoryItemData => ActorInventoryItemData::fromArray($item),
				$payload['inventory'] ?? [],
			),
			imagePath: self::normalizeImagePath($payload['image_path'] ?? null),
			meta: $payload['meta'] ?? null,
		);
	}

	/**
	 * Нормализует путь изображения актора внутри storage.
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
