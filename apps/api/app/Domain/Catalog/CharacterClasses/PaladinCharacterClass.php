<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\CharacterSubclasses\OathOfDevotionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfGloryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfTheAncientsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfVengeanceCharacterSubclass;

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
	 * @return list<\App\Domain\Catalog\Ability>
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
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Catalog\Items\ChainMailItem::class),
		];
	}
}
