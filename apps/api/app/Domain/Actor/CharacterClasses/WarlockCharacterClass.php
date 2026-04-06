<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\CharacterSubclasses\ArchfeyPatronCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\CelestialPatronCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\FiendPatronCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\GreatOldOnePatronCharacterSubclass;

/**
 * Сущность класса колдуна.
 */
final class WarlockCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'warlock';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Колдун / Чернокнижник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Заклинатель, получивший силу через договор с могущественным потусторонним покровителем.';
	}

	/**
	 * Возвращает бонусы характеристик колдуна.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает основные характеристики колдуна.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new CharismaAbility];
	}

	/**
	 * Возвращает подклассы колдуна.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new ArchfeyPatronCharacterSubclass,
			new CelestialPatronCharacterSubclass,
			new FiendPatronCharacterSubclass,
			new GreatOldOnePatronCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей колдуна по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение колдуна.
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
