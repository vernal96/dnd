<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheLandCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheMoonCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheSeaCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheStarsCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\DruidicFocusItem;
use App\Domain\Catalog\Items\HerbsOrHerbalismKitItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\QuarterstaffItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Items\WoodenShieldItem;

/**
 * Сущность класса друида.
 */
final class DruidCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'druid';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Друид';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хранитель природных сил, использующий первобытную магию и меняющий облик.';
	}

	/**
	 * Возвращает подклассы друида.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CircleOfTheLandCharacterSubclass,
			new CircleOfTheMoonCharacterSubclass,
			new CircleOfTheSeaCharacterSubclass,
			new CircleOfTheStarsCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение друида.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(QuarterstaffItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(WoodenShieldItem::class),
			$this->makeStartingEquipmentEntry(DruidicFocusItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(HerbsOrHerbalismKitItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
