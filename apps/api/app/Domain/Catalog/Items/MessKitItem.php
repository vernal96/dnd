<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Котелок".
 */
final class MessKitItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'mess-kit';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Котелок';
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
		return 'Компактный набор походной посуды для приготовления и употребления простой пищи.';
	}
}
