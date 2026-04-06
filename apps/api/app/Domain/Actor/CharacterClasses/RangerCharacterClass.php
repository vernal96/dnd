<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\WisdomAbility;
use App\Domain\Actor\CharacterSubclasses\BeastMasterCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\FeyWandererCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\GloomStalkerCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\HunterCharacterSubclass;

/**
 * Сущность класса следопыта.
 */
final class RangerCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'ranger';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Следопыт';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Охотник и разведчик приграничья, совмещающий меткость, выживание и магию пути.';
	}

	/**
	 * Возвращает бонусы характеристик следопыта.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 1, wisdom: 1);
	}

	/**
	 * Возвращает основные характеристики следопыта.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new DexterityAbility, new WisdomAbility];
	}

	/**
	 * Возвращает подклассы следопыта.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new BeastMasterCharacterSubclass,
			new FeyWandererCharacterSubclass,
			new GloomStalkerCharacterSubclass,
			new HunterCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей следопыта по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение следопыта.
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
