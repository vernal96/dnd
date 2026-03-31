<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AberrantSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ClockworkSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DraconicSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WildMagicCharacterSubclass;
use App\Domain\Catalog\Items\ArcaneFocusItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\CrossbowBoltsItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\LightCrossbowItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\ArcaneApotheosisSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\FontOfMagicSkill;
use App\Domain\Catalog\Skills\InnateSorcerySkill;
use App\Domain\Catalog\Skills\MetamagicSkill;
use App\Domain\Catalog\Skills\SorcererSubclassFeatureSkill;
use App\Domain\Catalog\Skills\SorcererSubclassSkill;
use App\Domain\Catalog\Skills\SorcerousRestorationSkill;
use App\Domain\Catalog\Skills\SorceryIncarnateSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;

/**
 * Сущность класса чародея.
 */
final class SorcererCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'sorcerer';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Чародей';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Носитель врожденной магии, чья сила исходит из крови, судьбы или иного внутреннего источника.';
	}

	/**
	 * Возвращает подклассы чародея.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AberrantSorceryCharacterSubclass,
			new ClockworkSorceryCharacterSubclass,
			new DraconicSorceryCharacterSubclass,
			new WildMagicCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей чародея по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new SpellcastingSkill, new InnateSorcerySkill],
			level2: [new FontOfMagicSkill, new MetamagicSkill],
			level3: [new SorcererSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new SorcerousRestorationSkill],
			level6: [new SorcererSubclassFeatureSkill],
			level7: [new SorceryIncarnateSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [],
			level10: [new MetamagicSkill],
			level11: [],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new SorcererSubclassFeatureSkill],
			level15: [],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new MetamagicSkill],
			level18: [new SorcererSubclassFeatureSkill],
			level19: [new EpicBoonSkill],
			level20: [new ArcaneApotheosisSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение чародея.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LightCrossbowItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(ArcaneFocusItem::class),
			$this->makeStartingEquipmentEntry(CrossbowBoltsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
