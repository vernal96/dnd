<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\IntelligenceAbility;
use App\Domain\Actor\CharacterSubclasses\AbjurerCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\DivinerCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\EvokerCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\IllusionistCharacterSubclass;

/**
 * Сущность класса волшебника.
 */
final class WizardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'wizard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Волшебник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Учёный магии, добивающийся могущества дисциплиной, исследованиями и точным знанием заклинаний.';
	}

	/**
	 * Возвращает бонусы характеристик волшебника.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(intelligence: 2);
	}

	/**
	 * Возвращает основные характеристики волшебника.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new IntelligenceAbility];
	}

	/**
	 * Возвращает подклассы волшебника.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AbjurerCharacterSubclass,
			new DivinerCharacterSubclass,
			new EvokerCharacterSubclass,
			new IllusionistCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей волшебника по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение волшебника.
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
