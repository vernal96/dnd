<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Actor\Item;

/**
 * Хранит одну запись стартового снаряжения класса персонажа.
 */
final readonly class StartingEquipmentEntryData
{
	/**
	 * Создает DTO записи стартового снаряжения.
	 */
	public function __construct(
		public string $itemClass,
		public int $quantity = 1,
	)
	{
	}

	/**
	 * Возвращает экземпляр предмета записи.
	 */
	public function getItem(): Item
	{
		$itemClass = $this->itemClass;

		return new $itemClass;
	}

}
