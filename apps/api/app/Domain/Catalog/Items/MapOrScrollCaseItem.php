<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Футляр для карт/свитков".
 */
final class MapOrScrollCaseItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'map-or-scroll-case';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Футляр для карт/свитков';
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
		return 'containers';
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Жёсткий футляр для карт, свитков и других свёрнутых документов.';
	}
}
