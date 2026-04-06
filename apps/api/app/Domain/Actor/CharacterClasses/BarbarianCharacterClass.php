<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\CharacterSubclasses\PathOfTheBerserkerCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\PathOfTheWildHeartCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\PathOfTheWorldTreeCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\PathOfTheZealotCharacterSubclass;

/**
 * Сущность класса варвара.
 */
final class BarbarianCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'barbarian';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Варвар';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Яростный воин, полагающийся на силу, стойкость и боевое неистовство.';
	}

	/**
	 * Возвращает бонусы характеристик варвара.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(strength: 1, constitution: 1);
	}

	/**
	 * Возвращает основные характеристики варвара.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new StrengthAbility, new ConstitutionAbility];
	}

	/**
	 * Возвращает подклассы варвара.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new PathOfTheBerserkerCharacterSubclass,
			new PathOfTheWildHeartCharacterSubclass,
			new PathOfTheWorldTreeCharacterSubclass,
			new PathOfTheZealotCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей варвара по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение варвара.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(\App\Domain\Actor\Items\LongswordItem::class),
		];
	}
}
