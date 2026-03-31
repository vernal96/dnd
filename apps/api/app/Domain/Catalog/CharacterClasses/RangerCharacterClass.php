<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\BeastMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FeyWandererCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GloomStalkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\HunterCharacterSubclass;
use App\Domain\Catalog\Items\ArrowsItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\CloakItem;
use App\Domain\Catalog\Items\LongbowItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\ShortswordItem;
use App\Domain\Catalog\Items\StuddedLeatherArmorItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\DeftExplorerSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ExpertiseSkill;
use App\Domain\Catalog\Skills\ExtraAttackSkill;
use App\Domain\Catalog\Skills\FavoredEnemySkill;
use App\Domain\Catalog\Skills\FeralSensesSkill;
use App\Domain\Catalog\Skills\FightingStyleSkill;
use App\Domain\Catalog\Skills\FoeSlayerSkill;
use App\Domain\Catalog\Skills\NaturesVeilSkill;
use App\Domain\Catalog\Skills\PreciseHunterSkill;
use App\Domain\Catalog\Skills\RangerSubclassFeatureSkill;
use App\Domain\Catalog\Skills\RangerSubclassSkill;
use App\Domain\Catalog\Skills\RelentlessHunterSkill;
use App\Domain\Catalog\Skills\RovingSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;
use App\Domain\Catalog\Skills\TirelessSkill;
use App\Domain\Catalog\Skills\WeaponMasterySkill;

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
			level1: [new SpellcastingSkill, new FavoredEnemySkill, new WeaponMasterySkill],
			level2: [new DeftExplorerSkill, new FightingStyleSkill],
			level3: [new RangerSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new ExtraAttackSkill],
			level6: [new RovingSkill],
			level7: [new RangerSubclassFeatureSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new ExpertiseSkill],
			level10: [new TirelessSkill],
			level11: [new RangerSubclassFeatureSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new RelentlessHunterSkill],
			level14: [new NaturesVeilSkill],
			level15: [new RangerSubclassFeatureSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new PreciseHunterSkill],
			level18: [new FeralSensesSkill],
			level19: [new EpicBoonSkill],
			level20: [new FoeSlayerSkill],
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
			$this->makeStartingEquipmentEntry(LongbowItem::class),
			$this->makeStartingEquipmentEntry(ShortswordItem::class),
			$this->makeStartingEquipmentEntry(StuddedLeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(ArrowsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(RopeItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
			$this->makeStartingEquipmentEntry(CloakItem::class),
		];
	}
}
