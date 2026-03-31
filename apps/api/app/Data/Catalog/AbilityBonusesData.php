<?php

declare(strict_types=1);

namespace App\Data\Catalog;

/**
 * Хранит полный набор бонусов характеристик с нулевыми значениями по умолчанию.
 */
final readonly class AbilityBonusesData
{
	/**
	 * Создает DTO бонусов характеристик.
	 */
	public function __construct(
		public int $strength = 0,
		public int $dexterity = 0,
		public int $constitution = 0,
		public int $intelligence = 0,
		public int $wisdom = 0,
		public int $charisma = 0,
	)
	{
	}

	/**
	 * Преобразует DTO в массив для API.
	 *
	 * @return array{str: int, dex: int, con: int, int: int, wis: int, cha: int}
	 */
	public function toArray(): array
	{
		return [
			'str' => $this->strength,
			'dex' => $this->dexterity,
			'con' => $this->constitution,
			'int' => $this->intelligence,
			'wis' => $this->wisdom,
			'cha' => $this->charisma,
		];
	}
}
