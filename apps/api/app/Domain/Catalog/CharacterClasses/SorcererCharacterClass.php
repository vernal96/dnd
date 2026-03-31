<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

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
use App\Domain\Catalog\Items\NoArmorItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;

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
	 * Возвращает стартовое снаряжение чародея.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LightCrossbowItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(NoArmorItem::class),
			$this->makeStartingEquipmentEntry(ArcaneFocusItem::class),
			$this->makeStartingEquipmentEntry(CrossbowBoltsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
