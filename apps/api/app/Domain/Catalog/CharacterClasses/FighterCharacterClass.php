<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\ConstitutionAbility;
use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\CharacterSubclasses\BattleMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ChampionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EldritchKnightCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PsiWarriorCharacterSubclass;

/**
 * Сущность класса воина.
 */
final class FighterCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'fighter';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Воин';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Универсальный мастер боя, добивающийся победы тренировкой, дисциплиной и техникой.';
	}

	/**
	 * Возвращает бонусы характеристик воина.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(strength: 1, constitution: 1);
	}

	/**
	 * Возвращает основные характеристики воина.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new StrengthAbility, new ConstitutionAbility];
	}

	/**
	 * Возвращает подклассы воина.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new BattleMasterCharacterSubclass,
			new ChampionCharacterSubclass,
			new EldritchKnightCharacterSubclass,
			new PsiWarriorCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей воина по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new \App\Domain\Catalog\Skills\WeaponMasterySkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение воина.
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
