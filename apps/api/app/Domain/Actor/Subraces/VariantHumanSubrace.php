<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Data\Catalog\AbilityBonusChoiceData;
use App\Domain\Actor\AbstractSubrace;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\IntelligenceAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\Abilities\WisdomAbility;

/**
 * Подраса вариативного человека.
 */
final class VariantHumanSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'variant-human';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Вариативный человек';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Люди с усиленной гибкостью развития и ранней специализацией.';
	}

	/**
	 * Возвращает варианты выбора бонусов характеристик вариативного человека.
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
					CharismaAbility::class,
				],
			),
		];
	}

}
