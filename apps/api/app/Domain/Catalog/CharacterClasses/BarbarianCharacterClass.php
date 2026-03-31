<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

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
