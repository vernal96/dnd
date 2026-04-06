<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\CharacterSubclasses\AberrantSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ClockworkSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DraconicSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WildMagicCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\BackpackItem::class),
		];
	}
}
