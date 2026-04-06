<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\Abilities\ConstitutionAbility;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Abilities\IntelligenceAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\Abilities\WisdomAbility;

/**
 * Базовая сущность класса персонажа, реализуемая конкретными классами.
 */
abstract class AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название класса персонажа.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание класса персонажа.
	 */
	abstract public function getDescription(): ?string;

	/**
	 * Возвращает признак активности класса персонажа.
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Возвращает признак доступности класса для выбора игроком.
	 */
	public function canBeSelectedByPlayer(): bool
	{
		return false;
	}

	/**
	 * Возвращает бонусы характеристик, которые дает класс персонажа.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData;
	}

	/**
	 * Возвращает основные характеристики класса персонажа.
	 *
	 * @return list<Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [];
	}

	/**
	 * Возвращает бонус класса персонажа к скорости.
	 */
	public function getSpeedBonus(): int
	{
		return 0;
	}

	/**
	 * Возвращает бонус класса персонажа к здоровью.
	 */
	public function getHealthBonus(): int
	{
		return 0;
	}

	/**
	 * Возвращает рекомендуемое автоматическое распределение 27 очков.
	 */
	public function getDefaultPointBuyAllocation(): AbilityBonusesData
	{
		$orderedClasses = array_values(array_unique([
			...array_map(
				static fn (Ability $ability): string => $ability::class,
				$this->getPrimaryAbilities(),
			),
			DexterityAbility::class,
			ConstitutionAbility::class,
			WisdomAbility::class,
			IntelligenceAbility::class,
			CharismaAbility::class,
			StrengthAbility::class,
		]));

		$valuesByClass = [
			$orderedClasses[0] ?? StrengthAbility::class => 8,
			$orderedClasses[1] ?? DexterityAbility::class => 8,
			$orderedClasses[2] ?? ConstitutionAbility::class => 4,
			$orderedClasses[3] ?? WisdomAbility::class => 3,
			$orderedClasses[4] ?? IntelligenceAbility::class => 2,
			$orderedClasses[5] ?? CharismaAbility::class => 2,
		];

		return new AbilityBonusesData(
			strength: $valuesByClass[StrengthAbility::class] ?? 0,
			dexterity: $valuesByClass[DexterityAbility::class] ?? 0,
			constitution: $valuesByClass[ConstitutionAbility::class] ?? 0,
			intelligence: $valuesByClass[IntelligenceAbility::class] ?? 0,
			wisdom: $valuesByClass[WisdomAbility::class] ?? 0,
			charisma: $valuesByClass[CharismaAbility::class] ?? 0,
		);
	}

	/**
	 * Возвращает только активные подклассы текущего класса персонажа.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getActiveSubclasses(): array
	{
		return array_values(array_filter(
			$this->getSubclasses(),
			static fn(AbstractCharacterSubclass $subclass): bool => $subclass->isActive(),
		));
	}

	/**
	 * Возвращает подклассы текущего класса персонажа.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [];
	}

	/**
	 * Возвращает распределение навыков класса персонажа по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData;
	}

	/**
	 * Возвращает стартовое снаряжение класса персонажа.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [];
	}

	/**
	 * Создает одну запись стартового снаряжения.
	 */
	protected function makeStartingEquipmentEntry(
		string $itemClass,
		int $quantity = 1,
	): StartingEquipmentEntryData {
		return new StartingEquipmentEntryData(
			itemClass: $itemClass,
			quantity: $quantity,
		);
	}
}
