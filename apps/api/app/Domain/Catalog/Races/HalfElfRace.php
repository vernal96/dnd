<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Data\Catalog\AbilityBonusChoiceData;
use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractRace;

/**
 * Сущность расы полуэльфа.
 */
final class HalfElfRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'half-elf';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Полуэльф';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Народ на стыке двух миров, сочетающий человеческую гибкость и эльфийскую утонченность.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик полуэльфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает варианты выбора бонусов характеристик полуэльфа.
	 *
	 * @return list<AbilityBonusChoiceData>
	 */
	public function getAbilityBonusChoices(): array
	{
		return [
			new AbilityBonusChoiceData(
				count: 2,
				value: 1,
				abilities: ['str', 'dex', 'con', 'int', 'wis'],
			),
		];
	}

}
