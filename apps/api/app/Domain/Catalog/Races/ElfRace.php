<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractRace;
use App\Domain\Catalog\AbstractSubrace;
use App\Domain\Catalog\Subraces\DrowElfSubrace;
use App\Domain\Catalog\Subraces\HighElfSubrace;
use App\Domain\Catalog\Subraces\WoodElfSubrace;

/**
 * Сущность расы эльфа.
 */
final class ElfRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'elf';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Эльф';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Древняя и изящная раса с сильной связью с природой, магией и традициями.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик эльфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 2);
	}

	/**
	 * Возвращает подрасы эльфа.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [
			new HighElfSubrace,
			new WoodElfSubrace,
			new DrowElfSubrace,
		];
	}
}
