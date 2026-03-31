<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Без брони".
 */
final class NoArmorItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'no-armor';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Без брони';
	}

	/**
	 * Возвращает тип предмета.
	 */
	public function getType(): ItemType
	{
		return ItemType::Armor;
	}

	/**
	 * Возвращает категорию предмета.
	 */
	public function getCategory(): string
	{
		return 'unarmored';
	}
}
