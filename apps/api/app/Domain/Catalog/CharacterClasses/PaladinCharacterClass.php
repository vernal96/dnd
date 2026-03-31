<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

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
