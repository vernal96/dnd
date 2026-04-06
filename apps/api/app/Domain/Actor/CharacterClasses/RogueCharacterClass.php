<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\CharacterSubclasses\ArcaneTricksterCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\AssassinCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\SoulknifeCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\ThiefCharacterSubclass;

/**
 * Сущность класса плута.
 */
final class RogueCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'rogue';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Плут';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хитрый специалист скрытности, ловкости и точечных ударов по уязвимым местам.';
	}

	/**
	 * Возвращает бонусы характеристик плута.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 2);
	}

	/**
	 * Возвращает основные характеристики плута.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new DexterityAbility];
	}

	/**
	 * Возвращает подклассы плута.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new ArcaneTricksterCharacterSubclass,
			new AssassinCharacterSubclass,
			new SoulknifeCharacterSubclass,
			new ThiefCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей плута по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение плута.
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
