<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\BattleMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ChampionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EldritchKnightCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PsiWarriorCharacterSubclass;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\ChainMailItem;
use App\Domain\Catalog\Items\CrossbowBoltsItem;
use App\Domain\Catalog\Items\LightCrossbowItem;
use App\Domain\Catalog\Items\LongswordItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\WaterskinItem;

/**
 * Сущность класса воина.
 */
final class FighterCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'fighter';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Воин';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Универсальный мастер боя, добивающийся победы тренировкой, дисциплиной и техникой.';
	}

	/**
	 * Возвращает подклассы воина.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new BattleMasterCharacterSubclass,
			new ChampionCharacterSubclass,
			new EldritchKnightCharacterSubclass,
			new PsiWarriorCharacterSubclass,
		];
	}

	/**
	 * Возвращает стартовое снаряжение воина.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [
			$this->makeStartingEquipmentEntry(LongswordItem::class),
			$this->makeStartingEquipmentEntry(LightCrossbowItem::class),
			$this->makeStartingEquipmentEntry(ChainMailItem::class),
			$this->makeStartingEquipmentEntry(ShieldItem::class),
			$this->makeStartingEquipmentEntry(CrossbowBoltsItem::class),
			$this->makeStartingEquipmentEntry(BackpackItem::class),
			$this->makeStartingEquipmentEntry(BedrollItem::class),
			$this->makeStartingEquipmentEntry(WaterskinItem::class),
			$this->makeStartingEquipmentEntry(RationsItem::class),
		];
	}
}
