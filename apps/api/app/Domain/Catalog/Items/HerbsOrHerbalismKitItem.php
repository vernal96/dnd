<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Травы или набор травника".
 */
final class HerbsOrHerbalismKitItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'herbs-or-herbalism-kit';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Травы или набор травника';
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
