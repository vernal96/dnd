<?php

declare(strict_types=1);

namespace App\Data\Catalog;

/**
 * Хранит один вариант выбора бонусов характеристик.
 */
final readonly class AbilityBonusChoiceData
{
	/**
	 * Создает DTO варианта выбора бонусов характеристик.
	 *
	 * @param list<string> $abilities
	 */
	public function __construct(
		public int $count,
		public int $value,
		public array $abilities,
	)
	{
	}

	/**
	 * Преобразует DTO в массив для API.
	 *
	 * @return array{count: int, value: int, abilities: list<string>}
	 */
	public function toArray(): array
	{
		return [
			'count' => $this->count,
			'value' => $this->value,
			'abilities' => $this->abilities,
		];
	}
}
