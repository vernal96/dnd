<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractRace;
use App\Domain\Catalog\AbstractSubrace;
use App\Domain\Catalog\Subraces\ForestGnomeSubrace;
use App\Domain\Catalog\Subraces\RockGnomeSubrace;

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
