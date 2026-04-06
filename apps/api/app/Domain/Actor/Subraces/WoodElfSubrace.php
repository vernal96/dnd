<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractSubrace;

/**
 * Подраса лесного эльфа.
 */
final class WoodElfSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'wood-elf';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Лесной эльф';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Эльфы чащ, следопыты и хранители диких земель.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик лесного эльфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(wisdom: 1);
	}

}
