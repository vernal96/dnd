<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\ConstitutionAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheBerserkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWildHeartCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWorldTreeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheZealotCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\LongswordItem::class),
		];
	}
}
