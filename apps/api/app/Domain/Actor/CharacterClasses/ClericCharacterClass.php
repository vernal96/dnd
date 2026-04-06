<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\WisdomAbility;
use App\Domain\Actor\CharacterSubclasses\LifeDomainCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\LightDomainCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\TrickeryDomainCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\WarDomainCharacterSubclass;

/**
 * Сущность класса жреца.
 */
final class ClericCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'cleric';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Жрец';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Проводник божественной силы, сочетающий молитвы, поддержку и священное возмездие.';
	}

	/**
	 * Возвращает бонусы характеристик жреца.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(wisdom: 2);
	}

	/**
	 * Возвращает основные характеристики жреца.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new WisdomAbility];
	}

	/**
	 * Возвращает подклассы жреца.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new LifeDomainCharacterSubclass,
			new LightDomainCharacterSubclass,
			new TrickeryDomainCharacterSubclass,
			new WarDomainCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей жреца по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение жреца.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(\App\Domain\Actor\Items\ChainMailItem::class),
		];
	}
}
