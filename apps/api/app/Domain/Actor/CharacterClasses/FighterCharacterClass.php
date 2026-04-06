<?php

declare(strict_types=1);

namespace App\Domain\Actor\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractCharacterSubclass;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\CharacterSubclasses\BattleMasterCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\ChampionCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\EldritchKnightCharacterSubclass;
use App\Domain\Actor\CharacterSubclasses\PsiWarriorCharacterSubclass;

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
	 * @return list<\App\Domain\Actor\Ability>
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
			level1: [new \App\Domain\Actor\Skills\WeaponMasterySkill],
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
			$this->makeStartingEquipmentEntry(\App\Domain\Actor\Items\ChainMailItem::class),
		];
	}
}
