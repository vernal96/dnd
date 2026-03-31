<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfDanceCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfGlamourCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfLoreCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfValorCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\MusicalInstrumentItem;
use App\Domain\Catalog\Items\PaperParchmentItem;
use App\Domain\Catalog\Items\QuillItem;
use App\Domain\Catalog\Items\RapierItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса барда.
 */
final class BardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'bard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Бард';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Мастер вдохновения, магии и искусства, меняющий ход событий словом и мелодией.';
	}

	/**
	 * Возвращает подклассы барда.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new CollegeOfDanceCharacterSubclass,
			new CollegeOfGlamourCharacterSubclass,
			new CollegeOfLoreCharacterSubclass,
			new CollegeOfValorCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение барда.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(RapierItem::class),
			$this->makeStartingEquipmentEntry(DaggerItem::class),
			$this->makeStartingEquipmentEntry(LeatherArmorItem::class),
			$this->makeStartingEquipmentEntry(MusicalInstrumentItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(PaperParchmentItem::class),
			$this->makeStartingEquipmentEntry(QuillItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
