<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AbjurerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DivinerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EvokerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\IllusionistCharacterSubclass;
use App\Domain\Catalog\Items\ArcaneFocusItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\InkAndQuillItem;
use App\Domain\Catalog\Items\NoArmorItem;
use App\Domain\Catalog\Items\QuarterstaffItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\SpellbookItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса волшебника.
 */
final class WizardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'wizard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Волшебник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Учёный магии, добивающийся могущества дисциплиной, исследованиями и точным знанием заклинаний.';
	}

	/**
	 * Возвращает подклассы волшебника.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AbjurerCharacterSubclass,
			new DivinerCharacterSubclass,
			new EvokerCharacterSubclass,
			new IllusionistCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение волшебника.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(QuarterstaffItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(NoArmorItem::class),
			$this->makeStartingEquipmentEntry(SpellbookItem::class),
			$this->makeStartingEquipmentEntry(ArcaneFocusItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(InkAndQuillItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
