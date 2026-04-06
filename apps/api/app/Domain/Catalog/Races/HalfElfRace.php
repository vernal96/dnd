<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Data\Catalog\AbilityBonusChoiceData;
use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractRace;
use App\Domain\Catalog\Abilities\ConstitutionAbility;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Abilities\IntelligenceAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\Abilities\WisdomAbility;

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
				abilities: [
					StrengthAbility::class,
					DexterityAbility::class,
					ConstitutionAbility::class,
					IntelligenceAbility::class,
					WisdomAbility::class,
				],
			),
		];
	}

}
