<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfMercyCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfShadowCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheElementsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheOpenHandCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\ShortswordItem;
use App\Domain\Catalog\Items\TravelerPackItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса монаха.
 */
final class MonkCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'monk';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Монах';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Воин внутренней дисциплины, направляющий энергию тела и духа в сверхчеловеческое мастерство.';
	}

	/**
	 * Возвращает подклассы монаха.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new WarriorOfMercyCharacterSubclass,
			new WarriorOfShadowCharacterSubclass,
			new WarriorOfTheElementsCharacterSubclass,
			new WarriorOfTheOpenHandCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение монаха.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(ShortswordItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(TravelerPackItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(RopeItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
