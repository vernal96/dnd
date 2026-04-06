<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\CharacterSubclasses\OathOfDevotionCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\OathOfGloryCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\OathOfTheAncientsCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\OathOfVengeanceCharacterSubclass;

/**
 * Сущность класса паладина.
 */
final class PaladinCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'paladin';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Паладин';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Священный воитель, следующий клятве и соединяющий веру, сталь и исцеляющий свет.';
	}

	/**
	 * Возвращает бонусы характеристик паладина.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(strength: 1, charisma: 1);
	}

	/**
	 * Возвращает основные характеристики паладина.
	 *
	 * @return list<\App\Domain\Actor\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new StrengthAbility, new CharismaAbility];
	}

	/**
	 * Возвращает подклассы паладина.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new OathOfDevotionCharacterSubclass,
			new OathOfGloryCharacterSubclass,
			new OathOfTheAncientsCharacterSubclass,
			new OathOfVengeanceCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей паладина по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение паладина.
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
