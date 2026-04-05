<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Abilities\WisdomAbility;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfMercyCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfShadowCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheElementsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheOpenHandCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\ShortswordItem;
use App\Domain\Catalog\Items\TravelerPackItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\AcrobaticMovementSkill;
use App\Domain\Catalog\Skills\BodyAndMindSkill;
use App\Domain\Catalog\Skills\DeflectAttacksSkill;
use App\Domain\Catalog\Skills\DeflectEnergySkill;
use App\Domain\Catalog\Skills\DisciplinedSurvivorSkill;
use App\Domain\Catalog\Skills\EmpoweredStrikesSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\EvasionSkill;
use App\Domain\Catalog\Skills\ExtraAttackSkill;
use App\Domain\Catalog\Skills\HeightenedFocusSkill;
use App\Domain\Catalog\Skills\MartialArtsSkill;
use App\Domain\Catalog\Skills\MonksFocusSkill;
use App\Domain\Catalog\Skills\MonkSubclassFeatureSkill;
use App\Domain\Catalog\Skills\MonkSubclassSkill;
use App\Domain\Catalog\Skills\MonkUnarmoredDefenseSkill;
use App\Domain\Catalog\Skills\PerfectFocusSkill;
use App\Domain\Catalog\Skills\SelfRestorationSkill;
use App\Domain\Catalog\Skills\SlowFallSkill;
use App\Domain\Catalog\Skills\StunningStrikeSkill;
use App\Domain\Catalog\Skills\SuperiorDefenseSkill;
use App\Domain\Catalog\Skills\UnarmoredMovementSkill;
use App\Domain\Catalog\Skills\UncannyMetabolismSkill;

/**
 * Сущность класса монаха.
 */
final class MonkCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'monk';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Монах';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Воин внутренней дисциплины, направляющий энергию тела и духа в сверхчеловеческое мастерство.';
	}

	/**
	 * Возвращает бонусы характеристик монаха.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 1, wisdom: 1);
	}

	/**
	 * Возвращает основные характеристики монаха.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new DexterityAbility, new WisdomAbility];
	}

	/**
	 * Возвращает подклассы монаха.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new WarriorOfMercyCharacterSubclass,
			new WarriorOfShadowCharacterSubclass,
			new WarriorOfTheElementsCharacterSubclass,
			new WarriorOfTheOpenHandCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей монаха по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new MartialArtsSkill, new MonkUnarmoredDefenseSkill],
			level2: [new MonksFocusSkill, new UnarmoredMovementSkill, new UncannyMetabolismSkill],
			level3: [new DeflectAttacksSkill, new MonkSubclassSkill],
			level4: [new AbilityScoreImprovementSkill, new SlowFallSkill],
			level5: [new ExtraAttackSkill, new StunningStrikeSkill],
			level6: [new EmpoweredStrikesSkill, new MonkSubclassFeatureSkill],
			level7: [new EvasionSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new AcrobaticMovementSkill],
			level10: [new HeightenedFocusSkill, new SelfRestorationSkill],
			level11: [new MonkSubclassFeatureSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new DeflectEnergySkill],
			level14: [new DisciplinedSurvivorSkill],
			level15: [new PerfectFocusSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new MonkSubclassFeatureSkill],
			level18: [new SuperiorDefenseSkill],
			level19: [new EpicBoonSkill],
			level20: [new BodyAndMindSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение монаха.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(ShortswordItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(TravelerPackItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(RopeItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
