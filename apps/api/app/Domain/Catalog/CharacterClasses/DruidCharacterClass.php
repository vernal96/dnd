<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\WisdomAbility;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheLandCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheMoonCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheSeaCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheStarsCharacterSubclass;

/**
 * Сущность класса друида.
 */
final class DruidCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'druid';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Друид';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хранитель природных сил, использующий первобытную магию и меняющий облик.';
	}

	/**
	 * Возвращает бонусы характеристик друида.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(wisdom: 2);
	}

	/**
	 * Возвращает основные характеристики друида.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new WisdomAbility];
	}

	/**
	 * Возвращает подклассы друида.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CircleOfTheLandCharacterSubclass,
			new CircleOfTheMoonCharacterSubclass,
			new CircleOfTheSeaCharacterSubclass,
			new CircleOfTheStarsCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей друида по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение друида.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\BackpackItem::class),
		];
	}
}
