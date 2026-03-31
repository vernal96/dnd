<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса высшего эльфа.
 */
final class HighElfSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'high-elf';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Высший эльф';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Эльфы учености, магии и древних башен.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик высшего эльфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(intelligence: 1);
	}

}
