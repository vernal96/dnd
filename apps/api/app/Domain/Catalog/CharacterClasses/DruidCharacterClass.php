<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheLandCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheMoonCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheSeaCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheStarsCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\DruidicFocusItem;
use App\Domain\Catalog\Items\HerbalismKitItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\QuarterstaffItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\ArchdruidSkill;
use App\Domain\Catalog\Skills\BeastSpellsSkill;
use App\Domain\Catalog\Skills\DruidicSkill;
use App\Domain\Catalog\Skills\DruidSubclassFeatureSkill;
use App\Domain\Catalog\Skills\DruidSubclassSkill;
use App\Domain\Catalog\Skills\ElementalFurySkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ImprovedElementalFurySkill;
use App\Domain\Catalog\Skills\PrimalOrderSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;
use App\Domain\Catalog\Skills\WildCompanionSkill;
use App\Domain\Catalog\Skills\WildResurgenceSkill;
use App\Domain\Catalog\Skills\WildShapeSkill;

/**
 * Сущность класса друида.
 */
final class DruidCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'druid';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Друид';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хранитель природных сил, использующий первобытную магию и меняющий облик.';
	}

	/**
	 * Возвращает подклассы друида.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CircleOfTheLandCharacterSubclass,
			new CircleOfTheMoonCharacterSubclass,
			new CircleOfTheSeaCharacterSubclass,
			new CircleOfTheStarsCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей друида по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new SpellcastingSkill, new DruidicSkill, new PrimalOrderSkill],
			level2: [new WildShapeSkill, new WildCompanionSkill],
			level3: [new DruidSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new WildResurgenceSkill],
			level6: [new DruidSubclassFeatureSkill],
			level7: [new ElementalFurySkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [],
			level10: [new DruidSubclassFeatureSkill],
			level11: [],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new DruidSubclassFeatureSkill],
			level15: [new ImprovedElementalFurySkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [],
			level18: [new BeastSpellsSkill],
			level19: [new EpicBoonSkill],
			level20: [new ArchdruidSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение друида.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(QuarterstaffItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(DruidicFocusItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(HerbalismKitItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
