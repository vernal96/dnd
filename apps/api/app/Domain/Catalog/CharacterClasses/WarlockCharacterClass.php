<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\CharacterSubclasses\ArchfeyPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CelestialPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FiendPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GreatOldOnePatronCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\BackpackItem::class),
		];
	}
}
