<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\BeastMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FeyWandererCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GloomStalkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\HunterCharacterSubclass;
use App\Domain\Catalog\Items\ArrowsItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\CloakItem;
use App\Domain\Catalog\Items\LongbowItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\ShortswordItem;
use App\Domain\Catalog\Items\StuddedLeatherArmorItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса следопыта.
 */
final class RangerCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'ranger';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Следопыт';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Охотник и разведчик приграничья, совмещающий меткость, выживание и магию пути.';
	}

	/**
	 * Возвращает подклассы следопыта.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new BeastMasterCharacterSubclass,
			new FeyWandererCharacterSubclass,
			new GloomStalkerCharacterSubclass,
			new HunterCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение следопыта.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LongbowItem::class),
			$this->makeStartingEquipmentEntry(ShortswordItem::class),
			$this->makeStartingEquipmentEntry(StuddedLeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(ArrowsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(RopeItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
			$this->makeStartingEquipmentEntry(CloakItem::class),
		];
	}
}
