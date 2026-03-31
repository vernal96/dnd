<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Масляный фонарь".
 */
final class HoodedLanternItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'hooded-lantern';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Масляный фонарь';
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
		return 'Фонарь с заслонкой, позволяющий направлять и прикрывать свет.';
	}
}
