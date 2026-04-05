<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\CharacterSubclasses\ArchfeyPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CelestialPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FiendPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GreatOldOnePatronCharacterSubclass;
use App\Domain\Catalog\Items\ArcaneFocusItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\CrossbowBoltsItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\LightCrossbowItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\ContactPatronSkill;
use App\Domain\Catalog\Skills\EldritchInvocationsSkill;
use App\Domain\Catalog\Skills\EldritchMasterSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\MagicalCunningSkill;
use App\Domain\Catalog\Skills\MysticArcanumSkill;
use App\Domain\Catalog\Skills\PactMagicSkill;
use App\Domain\Catalog\Skills\WarlockSubclassFeatureSkill;
use App\Domain\Catalog\Skills\WarlockSubclassSkill;

/**
 * Сущность класса колдуна.
 */
final class WarlockCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'warlock';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Колдун / Чернокнижник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Заклинатель, получивший силу через договор с могущественным потусторонним покровителем.';
	}

	/**
	 * Возвращает бонусы характеристик колдуна.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает основные характеристики колдуна.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new CharismaAbility];
	}

	/**
	 * Возвращает подклассы колдуна.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new ArchfeyPatronCharacterSubclass,
			new CelestialPatronCharacterSubclass,
			new FiendPatronCharacterSubclass,
			new GreatOldOnePatronCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей колдуна по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new EldritchInvocationsSkill, new PactMagicSkill],
			level2: [new MagicalCunningSkill],
			level3: [new WarlockSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [],
			level6: [new WarlockSubclassFeatureSkill],
			level7: [],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new ContactPatronSkill],
			level10: [new WarlockSubclassFeatureSkill],
			level11: [new MysticArcanumSkill(6)],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new MysticArcanumSkill(7)],
			level14: [new WarlockSubclassFeatureSkill],
			level15: [new MysticArcanumSkill(8)],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new MysticArcanumSkill(9)],
			level18: [],
			level19: [new EpicBoonSkill],
			level20: [new EldritchMasterSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение колдуна.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LightCrossbowItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(ArcaneFocusItem::class),
			$this->makeStartingEquipmentEntry(CrossbowBoltsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
