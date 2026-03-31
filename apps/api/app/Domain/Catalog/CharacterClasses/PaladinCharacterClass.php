<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\CharacterClassSkillProgressionData;
use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfDevotionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfGloryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfTheAncientsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfVengeanceCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\ChainMailItem;
use App\Domain\Catalog\Items\HolySymbolItem;
use App\Domain\Catalog\Items\JavelinItem;
use App\Domain\Catalog\Items\LongswordItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Skills\AbilityScoreImprovementSkill;
use App\Domain\Catalog\Skills\AbjureFoesSkill;
use App\Domain\Catalog\Skills\AuraExpansionSkill;
use App\Domain\Catalog\Skills\AuraOfCourageSkill;
use App\Domain\Catalog\Skills\AuraOfProtectionSkill;
use App\Domain\Catalog\Skills\ChannelDivinitySkill;
use App\Domain\Catalog\Skills\EpicBoonSkill;
use App\Domain\Catalog\Skills\ExtraAttackSkill;
use App\Domain\Catalog\Skills\FaithfulSteedSkill;
use App\Domain\Catalog\Skills\FightingStyleSkill;
use App\Domain\Catalog\Skills\LayOnHandsSkill;
use App\Domain\Catalog\Skills\PaladinsSmiteSkill;
use App\Domain\Catalog\Skills\PaladinSubclassFeatureSkill;
use App\Domain\Catalog\Skills\PaladinSubclassSkill;
use App\Domain\Catalog\Skills\RadiantStrikesSkill;
use App\Domain\Catalog\Skills\RestoringTouchSkill;
use App\Domain\Catalog\Skills\SpellcastingSkill;
use App\Domain\Catalog\Skills\WeaponMasterySkill;

/**
 * Сущность класса паладина.
 */
final class PaladinCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'paladin';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Паладин';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Священный воитель, следующий клятве и соединяющий веру, сталь и исцеляющий свет.';
	}

	/**
	 * Возвращает подклассы паладина.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new OathOfDevotionCharacterSubclass,
			new OathOfGloryCharacterSubclass,
			new OathOfTheAncientsCharacterSubclass,
			new OathOfVengeanceCharacterSubclass,
		];
	}

	/**
	 * Возвращает прогрессию классовых способностей паладина по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData(
			level1: [new LayOnHandsSkill, new SpellcastingSkill, new WeaponMasterySkill],
			level2: [new FightingStyleSkill, new PaladinsSmiteSkill],
			level3: [new ChannelDivinitySkill, new PaladinSubclassSkill],
			level4: [new AbilityScoreImprovementSkill],
			level5: [new ExtraAttackSkill, new FaithfulSteedSkill],
			level6: [new AuraOfProtectionSkill],
			level7: [new PaladinSubclassFeatureSkill],
			level8: [new AbilityScoreImprovementSkill],
			level9: [new AbjureFoesSkill],
			level10: [new AuraOfCourageSkill],
			level11: [new RadiantStrikesSkill],
			level12: [new AbilityScoreImprovementSkill],
			level13: [],
			level14: [new RestoringTouchSkill],
			level15: [new PaladinSubclassFeatureSkill],
			level16: [new AbilityScoreImprovementSkill],
			level17: [],
			level18: [new AuraExpansionSkill],
			level19: [new EpicBoonSkill],
			level20: [new PaladinSubclassFeatureSkill],
		);
	}

	/**
	 * Возвращает стартовое снаряжение паладина.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LongswordItem::class),
			$this->makeStartingEquipmentEntry(JavelinItem::class, 5),
			$this->makeStartingEquipmentEntry(ChainMailItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(HolySymbolItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
