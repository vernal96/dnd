<?php

declare(strict_types=1);

namespace App\Domain\Actor\Items;

use App\Domain\Actor\Item;
use App\Domain\Actor\ItemType;

/**
 * Сущность предмета "Рюкзак".
 */
final class BackpackItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'backpack';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Рюкзак';
	}

	/**
	 * Возвращает тип предмета.
	 */
	public function getType(): ItemType
	{
		return ItemType::Equipment;
	}

	/**
	 * Возвращает категорию предмета.
	 */
	public function getCategory(): string
	{
		return 'survival-and-travel';
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Рюкзак для переноски снаряжения. На него удобно крепить спальник, верёвку и другие вещи снаружи.';
	}
}
