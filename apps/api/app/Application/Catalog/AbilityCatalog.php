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
	 * Возвращает все классы характеристик в фиксированном порядке.
	 *
	 * @return list<class-string<Ability>>
	 */
	public function getAbilityClasses(): array
	{
		return [
			StrengthAbility::class,
			DexterityAbility::class,
			ConstitutionAbility::class,
			IntelligenceAbility::class,
			WisdomAbility::class,
			CharismaAbility::class,
		];
	}

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
	 * Возвращает код характеристики по имени ее класса.
	 *
	 * @param class-string<Ability> $abilityClass
	 */
	public function getCodeByClass(string $abilityClass): string
	{
		$ability = new $abilityClass;

		return $ability->getCode();
	}

	/**
	 * Возвращает все характеристики в фиксированном порядке.
	 *
	 * @return list<Ability>
	 */
	public function getAbilities(): array
	{
		return array_map(
			static fn (string $abilityClass): Ability => new $abilityClass,
			$this->getAbilityClasses(),
		);
	}
}
