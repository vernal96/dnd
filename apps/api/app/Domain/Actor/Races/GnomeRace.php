<?php

declare(strict_types=1);

namespace App\Domain\Actor\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractRace;
use App\Domain\Actor\AbstractSubrace;
use App\Domain\Actor\Subraces\ForestGnomeSubrace;
use App\Domain\Actor\Subraces\RockGnomeSubrace;

/**
 * Сущность расы гнома.
 */
final class GnomeRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'gnome';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Гном';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Любознательная и изобретательная раса, сочетающая острый ум, ремесло и чувство чудесного.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик гнома.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(intelligence: 2);
	}

	/**
	 * Возвращает подрасы гнома.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [
			new ForestGnomeSubrace,
			new RockGnomeSubrace,
		];
	}
}
