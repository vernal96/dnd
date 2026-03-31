<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
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
