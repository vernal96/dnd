<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Подзорная труба".
 */
final class SpyglassItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'spyglass';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Подзорная труба';
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
		return 'light-and-vision';
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Подзорная труба, увеличивающая удалённые объекты и полезная для наблюдения.';
	}
}
