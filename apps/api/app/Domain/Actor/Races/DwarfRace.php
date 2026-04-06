<?php

declare(strict_types=1);

namespace App\Domain\Actor\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractRace;
use App\Domain\Actor\AbstractSubrace;
use App\Domain\Actor\Subraces\HillDwarfSubrace;
use App\Domain\Actor\Subraces\MountainDwarfSubrace;

/**
 * Сущность расы дварфа.
 */
final class DwarfRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'dwarf';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Дварф';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Выносливая раса мастеров и воинов, привыкшая к подземельям, ремеслу и дисциплине.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик дварфа.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(constitution: 2);
	}

	/**
	 * Возвращает подрасы дварфа.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [
			new HillDwarfSubrace,
			new MountainDwarfSubrace,
		];
	}
}
