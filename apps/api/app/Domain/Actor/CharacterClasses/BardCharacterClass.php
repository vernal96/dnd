<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\CharacterSubclasses\CollegeOfDanceCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\CollegeOfGlamourCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\CollegeOfLoreCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\CollegeOfValorCharacterSubclass;

/**
 * Сущность класса барда.
 */
final class BardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'bard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Бард';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Мастер вдохновения, магии и искусства, меняющий ход событий словом и мелодией.';
	}

	/**
	 * Возвращает бонусы характеристик барда.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает основные характеристики барда.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new CharismaAbility, new DexterityAbility];
	}

	/**
	 * Возвращает подклассы барда.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CollegeOfDanceCharacterSubclass,
			new CollegeOfGlamourCharacterSubclass,
			new CollegeOfLoreCharacterSubclass,
			new CollegeOfValorCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей барда по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение барда.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(\App\Domain\Actor\Items\BackpackItem::class),
		];
	}
}
