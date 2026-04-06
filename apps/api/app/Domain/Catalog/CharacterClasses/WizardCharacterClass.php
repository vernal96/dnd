<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\IntelligenceAbility;
use App\Domain\Catalog\CharacterSubclasses\AbjurerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DivinerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EvokerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\IllusionistCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\BackpackItem::class),
		];
	}
}
