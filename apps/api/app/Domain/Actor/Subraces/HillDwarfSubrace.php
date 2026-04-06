<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractSubrace;

/**
 * Подраса холмового дварфа.
 */
final class HillDwarfSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'hill-dwarf';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Холмовой дварф';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Крепкие дварфы с упором на выносливость и традицию кланов.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик холмового дварфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(wisdom: 1);
	}

}
