<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\WisdomAbility;
use App\Domain\Catalog\CharacterSubclasses\LifeDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\LightDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\TrickeryDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarDomainCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\ChainShirtItem;
use App\Domain\Catalog\Items\HolySymbolItem;
use App\Domain\Catalog\Items\MaceItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\SpearItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\BlessedStrikesSkill;
use App\Domain\Catalog\Skills\ChannelDivinitySkill;
use App\Domain\Catalog\Skills\ClericSubclassFeatureSkill;
use App\Domain\Catalog\Skills\ClericSubclassSkill;
use App\Domain\Catalog\Skills\DivineInterventionSkill;
use App\Domain\Catalog\Skills\DivineOrderSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\GreaterDivineInterventionSkill;
use App\Domain\Catalog\Skills\ImprovedBlessedStrikesSkill;
use App\Domain\Catalog\Skills\SearUndeadSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;

/**
 * Сущность класса жреца.
 */
final class ClericCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'cleric';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Жрец';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Проводник божественной силы, сочетающий молитвы, поддержку и священное возмездие.';
	}

	/**
	 * Возвращает бонусы характеристик жреца.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(wisdom: 2);
	}

	/**
	 * Возвращает основные характеристики жреца.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new WisdomAbility];
	}

	/**
	 * Возвращает подклассы жреца.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new LifeDomainCharacterSubclass,
			new LightDomainCharacterSubclass,
			new TrickeryDomainCharacterSubclass,
			new WarDomainCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей жреца по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new SpellcastingSkill, new DivineOrderSkill],
			level2: [new ChannelDivinitySkill],
			level3: [new ClericSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new SearUndeadSkill],
			level6: [new ClericSubclassFeatureSkill],
			level7: [new BlessedStrikesSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [],
			level10: [new DivineInterventionSkill],
			level11: [],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new ImprovedBlessedStrikesSkill],
			level15: [],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new ClericSubclassFeatureSkill],
			level18: [],
			level19: [new EpicBoonSkill],
			level20: [new GreaterDivineInterventionSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение жреца.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(MaceItem::class),
			$this->makeStartingEquipmentEntry(SpearItem::class),
			$this->makeStartingEquipmentEntry(ChainShirtItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(HolySymbolItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
