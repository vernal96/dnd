<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Abilities\WisdomAbility;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfMercyCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfShadowCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheElementsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheOpenHandCharacterSubclass;

/**
 * Сущность класса монаха.
 */
final class MonkCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'monk';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Монах';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Воин внутренней дисциплины, направляющий энергию тела и духа в сверхчеловеческое мастерство.';
	}

	/**
	 * Возвращает бонусы характеристик монаха.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 1, wisdom: 1);
	}

	/**
	 * Возвращает основные характеристики монаха.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new DexterityAbility, new WisdomAbility];
	}

	/**
	 * Возвращает подклассы монаха.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new WarriorOfMercyCharacterSubclass,
			new WarriorOfShadowCharacterSubclass,
			new WarriorOfTheElementsCharacterSubclass,
			new WarriorOfTheOpenHandCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей монаха по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение монаха.
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
