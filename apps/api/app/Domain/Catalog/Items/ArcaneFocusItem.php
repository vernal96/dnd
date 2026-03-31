<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Магический фокус".
 */
final class ArcaneFocusItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'arcane-focus';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Магический фокус';
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
		return 'kits';
	}
}
