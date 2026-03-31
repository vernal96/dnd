<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Флаконы".
 */
final class VialsItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'vials';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Флаконы';
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
		return 'healing-and-alchemy';
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Малые сосуды для хранения небольших объёмов жидкостей и реагентов.';
	}
}
