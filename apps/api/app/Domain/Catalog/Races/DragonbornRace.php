<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractRace;

/**
 * Сущность расы драконорождённого.
 */
final class DragonbornRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'dragonborn';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Драконорождённый';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Гордый народ с драконьим наследием, для которого честь, сила и происхождение имеют особый вес.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик драконорождённого.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(
			strength: 2,
			charisma: 1,
		);
	}

}
