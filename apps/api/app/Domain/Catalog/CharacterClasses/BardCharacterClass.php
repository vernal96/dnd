<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\Abilities\CharismaAbility;
use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfDanceCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfGlamourCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfLoreCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfValorCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\MusicalInstrumentItem;
use App\Domain\Catalog\Items\PaperParchmentItem;
use App\Domain\Catalog\Items\QuillItem;
use App\Domain\Catalog\Items\RapierItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\BardicInspirationSkill;
use App\Domain\Catalog\Skills\BardSubclassFeatureSkill;
use App\Domain\Catalog\Skills\BardSubclassSkill;
use App\Domain\Catalog\Skills\CountercharmSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ExpertiseSkill;
use App\Domain\Catalog\Skills\FontOfInspirationSkill;
use App\Domain\Catalog\Skills\JackOfAllTradesSkill;
use App\Domain\Catalog\Skills\MagicalSecretsSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;
use App\Domain\Catalog\Skills\SuperiorInspirationSkill;
use App\Domain\Catalog\Skills\WordsOfCreationSkill;

/**
 * Сущность класса барда.
 */
final class BardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'bard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Бард';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Мастер вдохновения, магии и искусства, меняющий ход событий словом и мелодией.';
	}

	/**
	 * Возвращает бонусы характеристик барда.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 2);
	}

	/**
	 * Возвращает основные характеристики барда.
	 *
	 * @return list<\App\Domain\Catalog\Ability>
	 */
	public function getPrimaryAbilities(): array
	{
		return [new CharismaAbility, new DexterityAbility];
	}

	/**
	 * Возвращает подклассы барда.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CollegeOfDanceCharacterSubclass,
			new CollegeOfGlamourCharacterSubclass,
			new CollegeOfLoreCharacterSubclass,
			new CollegeOfValorCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей барда по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new BardicInspirationSkill, new SpellcastingSkill],
			level2: [new ExpertiseSkill, new JackOfAllTradesSkill],
			level3: [new BardSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new FontOfInspirationSkill],
			level6: [new BardSubclassFeatureSkill],
			level7: [new CountercharmSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new ExpertiseSkill],
			level10: [new MagicalSecretsSkill],
			level11: [],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new BardSubclassFeatureSkill],
			level15: [],
			level16: [new AbilityScoreImprovementSkill],
			level17: [],
			level18: [new SuperiorInspirationSkill],
			level19: [new EpicBoonSkill],
			level20: [new WordsOfCreationSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение барда.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(RapierItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(MusicalInstrumentItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(PaperParchmentItem::class),
			$this->makeStartingEquipmentEntry(QuillItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
