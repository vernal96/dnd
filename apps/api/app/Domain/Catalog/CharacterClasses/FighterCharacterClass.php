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
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\ChainMailItem;
use App\Domain\Catalog\Items\CrossbowBoltsItem;
use App\Domain\Catalog\Items\LightCrossbowItem;
use App\Domain\Catalog\Items\LongswordItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\ActionSurgeSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ExtraAttackSkill;
use App\Domain\Catalog\Skills\FighterSubclassFeatureSkill;
use App\Domain\Catalog\Skills\FighterSubclassSkill;
use App\Domain\Catalog\Skills\FightingStyleSkill;
use App\Domain\Catalog\Skills\IndomitableSkill;
use App\Domain\Catalog\Skills\SecondWindSkill;
use App\Domain\Catalog\Skills\StudiedAttacksSkill;
use App\Domain\Catalog\Skills\TacticalMasterSkill;
use App\Domain\Catalog\Skills\TacticalMindSkill;
use App\Domain\Catalog\Skills\TacticalShiftSkill;
use App\Domain\Catalog\Skills\ThreeExtraAttacksSkill;
use App\Domain\Catalog\Skills\TwoExtraAttacksSkill;
use App\Domain\Catalog\Skills\WeaponMasterySkill;

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
			level1: [new FightingStyleSkill, new SecondWindSkill, new WeaponMasterySkill],
			level2: [new ActionSurgeSkill, new TacticalMindSkill],
			level3: [new FighterSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new ExtraAttackSkill, new TacticalShiftSkill],
			level6: [new AbilityScoreImprovementSkill],
			level7: [new FighterSubclassFeatureSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new IndomitableSkill, new TacticalMasterSkill],
			level10: [new FighterSubclassFeatureSkill],
			level11: [new TwoExtraAttacksSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new IndomitableSkill, new StudiedAttacksSkill],
			level14: [new AbilityScoreImprovementSkill],
			level15: [new FighterSubclassFeatureSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new ActionSurgeSkill, new IndomitableSkill],
			level18: [new FighterSubclassFeatureSkill],
			level19: [new EpicBoonSkill],
			level20: [new ThreeExtraAttacksSkill],
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
			$this->makeStartingEquipmentEntry(LongswordItem::class),
			$this->makeStartingEquipmentEntry(LightCrossbowItem::class),
			$this->makeStartingEquipmentEntry(ChainMailItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(CrossbowBoltsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
