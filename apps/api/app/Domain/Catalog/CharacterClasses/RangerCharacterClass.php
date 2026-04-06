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
use App\Domain\Catalog\CharacterSubclasses\BeastMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FeyWandererCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GloomStalkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\HunterCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\LongswordItem::class),
		];
	}
}
