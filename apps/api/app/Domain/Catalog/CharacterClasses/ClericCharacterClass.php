<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\LifeDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\LightDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\TrickeryDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarDomainCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\ChainShirtItem;
use App\Domain\Catalog\Items\HolySymbolItem;
use App\Domain\Catalog\Items\MaceItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\SpearItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса жреца.
 */
final class ClericCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'cleric';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Жрец';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Проводник божественной силы, сочетающий молитвы, поддержку и священное возмездие.';
	}

	/**
	 * Возвращает подклассы жреца.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new LifeDomainCharacterSubclass,
			new LightDomainCharacterSubclass,
			new TrickeryDomainCharacterSubclass,
			new WarDomainCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение жреца.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(MaceItem::class),
			$this->makeStartingEquipmentEntry(SpearItem::class),
			$this->makeStartingEquipmentEntry(ChainShirtItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(HolySymbolItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
