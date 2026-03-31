<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

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
