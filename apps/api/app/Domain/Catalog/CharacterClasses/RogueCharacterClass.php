<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ArcaneTricksterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AssassinCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\SoulknifeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ThiefCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\RapierItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShortbowItem;
use App\Domain\Catalog\Items\ThievesToolsItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\CunningActionSkill;
use App\Domain\Catalog\Skills\CunningStrikeSkill;
use App\Domain\Catalog\Skills\DeviousStrikesSkill;
use App\Domain\Catalog\Skills\ElusiveSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\EvasionSkill;
use App\Domain\Catalog\Skills\ExpertiseSkill;
use App\Domain\Catalog\Skills\ImprovedCunningStrikeSkill;
use App\Domain\Catalog\Skills\ReliableTalentSkill;
use App\Domain\Catalog\Skills\RogueSubclassFeatureSkill;
use App\Domain\Catalog\Skills\RogueSubclassSkill;
use App\Domain\Catalog\Skills\SneakAttackSkill;
use App\Domain\Catalog\Skills\SlipperyMindSkill;
use App\Domain\Catalog\Skills\SteadyAimSkill;
use App\Domain\Catalog\Skills\StrokeOfLuckSkill;
use App\Domain\Catalog\Skills\ThievesCantSkill;
use App\Domain\Catalog\Skills\UncannyDodgeSkill;
use App\Domain\Catalog\Skills\WeaponMasterySkill;

/**
 * Сущность класса плута.
 */
final class RogueCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'rogue';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Плут';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хитрый специалист скрытности, ловкости и точечных ударов по уязвимым местам.';
	}

	/**
	 * Возвращает подклассы плута.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new ArcaneTricksterCharacterSubclass,
			new AssassinCharacterSubclass,
			new SoulknifeCharacterSubclass,
			new ThiefCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей плута по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new ExpertiseSkill, new SneakAttackSkill, new ThievesCantSkill, new WeaponMasterySkill],
			level2: [new CunningActionSkill],
			level3: [new RogueSubclassSkill, new SteadyAimSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new CunningStrikeSkill, new UncannyDodgeSkill],
			level6: [new ExpertiseSkill],
			level7: [new EvasionSkill, new ReliableTalentSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new RogueSubclassFeatureSkill],
			level10: [new AbilityScoreImprovementSkill],
			level11: [new ImprovedCunningStrikeSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new RogueSubclassFeatureSkill],
			level14: [new DeviousStrikesSkill],
			level15: [new SlipperyMindSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new RogueSubclassFeatureSkill],
			level18: [new ElusiveSkill],
			level19: [new EpicBoonSkill],
			level20: [new StrokeOfLuckSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение плута.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(RapierItem::class),
			$this->makeStartingEquipmentEntry(ShortbowItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(ThievesToolsItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
