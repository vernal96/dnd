<?php

declare(strict_types=1);

namespace App\Domain\Actor\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractRace;

/**
 * Сущность расы полуорка.
 */
final class HalfOrcRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'half-orc';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Полуорк';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Сильные и стойкие воины, привыкшие выживать между суровостью и предубеждением.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик полуорка.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(
			strength: 2,
			constitution: 1,
		);
	}

}
