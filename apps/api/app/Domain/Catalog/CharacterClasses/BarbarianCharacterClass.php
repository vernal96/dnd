<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheBerserkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWildHeartCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWorldTreeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheZealotCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\GreataxeItem;
use App\Domain\Catalog\Items\HandaxeItem;
use App\Domain\Catalog\Items\HideArmorItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\TorchesItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\BarbarianSubclassFeatureSkill;
use App\Domain\Catalog\Skills\BarbarianSubclassSkill;
use App\Domain\Catalog\Skills\BarbarianUnarmoredDefenseSkill;
use App\Domain\Catalog\Skills\BrutalStrikeSkill;
use App\Domain\Catalog\Skills\DangerSenseSkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ExtraAttackSkill;
use App\Domain\Catalog\Skills\FastMovementSkill;
use App\Domain\Catalog\Skills\FeralInstinctSkill;
use App\Domain\Catalog\Skills\ImprovedBrutalStrikeSkill;
use App\Domain\Catalog\Skills\IndomitableMightSkill;
use App\Domain\Catalog\Skills\InstinctivePounceSkill;
use App\Domain\Catalog\Skills\PersistentRageSkill;
use App\Domain\Catalog\Skills\PrimalChampionSkill;
use App\Domain\Catalog\Skills\PrimalKnowledgeSkill;
use App\Domain\Catalog\Skills\RageSkill;
use App\Domain\Catalog\Skills\RecklessAttackSkill;
use App\Domain\Catalog\Skills\RelentlessRageSkill;
use App\Domain\Catalog\Skills\WeaponMasterySkill;

/**
 * Сущность класса варвара.
 */
final class BarbarianCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'barbarian';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Варвар';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Яростный воин, полагающийся на силу, стойкость и боевое неистовство.';
	}

	/**
	 * Возвращает подклассы варвара.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new PathOfTheBerserkerCharacterSubclass,
			new PathOfTheWildHeartCharacterSubclass,
			new PathOfTheWorldTreeCharacterSubclass,
			new PathOfTheZealotCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей варвара по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new RageSkill, new BarbarianUnarmoredDefenseSkill, new WeaponMasterySkill],
			level2: [new DangerSenseSkill, new RecklessAttackSkill],
			level3: [new BarbarianSubclassSkill, new PrimalKnowledgeSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new ExtraAttackSkill, new FastMovementSkill],
			level6: [new BarbarianSubclassFeatureSkill],
			level7: [new FeralInstinctSkill, new InstinctivePounceSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new BrutalStrikeSkill],
			level10: [new BarbarianSubclassFeatureSkill],
			level11: [new RelentlessRageSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [new ImprovedBrutalStrikeSkill],
			level14: [new BarbarianSubclassFeatureSkill],
			level15: [new PersistentRageSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [new ImprovedBrutalStrikeSkill],
			level18: [new IndomitableMightSkill],
			level19: [new EpicBoonSkill],
			level20: [new PrimalChampionSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение варвара.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(GreataxeItem::class),
			$this->makeStartingEquipmentEntry(HandaxeItem::class, 2),
			$this->makeStartingEquipmentEntry(HideArmorItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
			$this->makeStartingEquipmentEntry(RopeItem::class),
			$this->makeStartingEquipmentEntry(TorchesItem::class),
		];
	}
}
