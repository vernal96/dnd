<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\CharacterSubclasses\AberrantSorceryCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\ClockworkSorceryCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\DraconicSorceryCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\WildMagicCharacterSubclass;

/**
 * Сущность класса чародея.
 */
final class SorcererCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'sorcerer';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Чародей';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Носитель врожденной магии, чья сила исходит из крови, судьбы или иного внутреннего источника.';
	}

	/**
	 * Возвращает бонусы характеристик чародея.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает основные характеристики чародея.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new CharismaAbility];
	}

	/**
	 * Возвращает подклассы чародея.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AberrantSorceryCharacterSubclass,
			new ClockworkSorceryCharacterSubclass,
			new DraconicSorceryCharacterSubclass,
			new WildMagicCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей чародея по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение чародея.
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
