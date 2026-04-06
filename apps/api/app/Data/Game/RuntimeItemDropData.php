<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает один runtime-дроп предмета для API-представления сцены.
 */
final readonly class RuntimeItemDropData
{
	/**
	 * Создает DTO runtime-дропа предмета.
	 */
	public function __construct(
		public string $id,
		public string $itemCode,
		public ?string $name,
		public int $quantity,
		public int $x,
		public int $y,
		public ?string $imageUrl,
	)
	{
	}
}
