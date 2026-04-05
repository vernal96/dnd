<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\Abilities\ConstitutionAbility;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Abilities\IntelligenceAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\Abilities\WisdomAbility;
use App\Domain\Catalog\Ability;

/**
 * Хранит кодовый справочник базовых характеристик персонажа.
 */
final class AbilityCatalog
{
	/**
	 * Возвращает одну характеристику по коду.
	 */
	public function findAbilityByCode(string $code): ?Ability
	{
		foreach ($this->getAbilities() as $ability) {
			if ($ability->getCode() === $code) {
				return $ability;
			}
		}

		return null;
	}

	/**
	 * Возвращает все характеристики в фиксированном порядке.
	 *
	 * @return list<Ability>
	 */
	public function getAbilities(): array
	{
		return [
			new StrengthAbility,
			new DexterityAbility,
			new ConstitutionAbility,
			new IntelligenceAbility,
			new WisdomAbility,
			new CharismaAbility,
		];
	}
}
