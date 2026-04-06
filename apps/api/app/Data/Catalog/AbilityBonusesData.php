<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Actor\Ability;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\IntelligenceAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\Abilities\WisdomAbility;

/**
 * Хранит полный набор бонусов характеристик с нулевыми значениями по умолчанию.
 */
final readonly class AbilityBonusesData
{
	/**
	 * Создает DTO бонусов характеристик.
	 */
	public function __construct(
		public int $strength = 0,
		public int $dexterity = 0,
		public int $constitution = 0,
		public int $intelligence = 0,
		public int $wisdom = 0,
		public int $charisma = 0,
	)
	{
	}

	/**
	 * Возвращает бонус по объекту характеристики.
	 */
	public function getByAbility(Ability $ability): int
	{
		return match ($ability::class) {
			StrengthAbility::class => $this->strength,
			DexterityAbility::class => $this->dexterity,
			ConstitutionAbility::class => $this->constitution,
			IntelligenceAbility::class => $this->intelligence,
			WisdomAbility::class => $this->wisdom,
			CharismaAbility::class => $this->charisma,
			default => 0,
		};
	}
}
