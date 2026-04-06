<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\CharacterSubclasses\ArcaneTricksterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AssassinCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\SoulknifeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ThiefCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\LongswordItem::class),
		];
	}
}
