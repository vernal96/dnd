<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AbjurerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DivinerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EvokerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\IllusionistCharacterSubclass;
use App\Domain\Catalog\Items\ArcaneFocusItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\InkItem;
use App\Domain\Catalog\Items\QuarterstaffItem;
use App\Domain\Catalog\Items\QuillItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\SpellbookItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\ArcaneRecoverySkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\MemorizeSpellSkill;
use App\Domain\Catalog\Skills\RitualAdeptSkill;
use App\Domain\Catalog\Skills\ScholarSkill;
use App\Domain\Catalog\Skills\SignatureSpellsSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;
use App\Domain\Catalog\Skills\SpellMasterySkill;
use App\Domain\Catalog\Skills\WizardSubclassFeatureSkill;
use App\Domain\Catalog\Skills\WizardSubclassSkill;

/**
 * Сущность класса волшебника.
 */
final class WizardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'wizard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Волшебник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Учёный магии, добивающийся могущества дисциплиной, исследованиями и точным знанием заклинаний.';
	}

	/**
	 * Возвращает подклассы волшебника.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AbjurerCharacterSubclass,
			new DivinerCharacterSubclass,
			new EvokerCharacterSubclass,
			new IllusionistCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей волшебника по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new SpellcastingSkill, new RitualAdeptSkill, new ArcaneRecoverySkill],
			level2: [new ScholarSkill],
			level3: [new WizardSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new MemorizeSpellSkill],
			level6: [new WizardSubclassFeatureSkill],
			level7: [],
			level8: [new AbilityScoreImprovementSkill],
			level9: [],
			level10: [new WizardSubclassFeatureSkill],
			level11: [],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new WizardSubclassFeatureSkill],
			level15: [],
			level16: [new AbilityScoreImprovementSkill],
			level17: [],
			level18: [new SpellMasterySkill],
			level19: [new EpicBoonSkill],
			level20: [new SignatureSpellsSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение волшебника.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(QuarterstaffItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(SpellbookItem::class),
			$this->makeStartingEquipmentEntry(ArcaneFocusItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(InkItem::class),
			$this->makeStartingEquipmentEntry(QuillItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
