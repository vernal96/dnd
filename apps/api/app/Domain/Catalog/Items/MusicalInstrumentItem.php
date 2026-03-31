<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Музыкальный инструмент".
 */
final class MusicalInstrumentItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'musical-instrument';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Музыкальный инструмент';
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
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Музыкальный инструмент для исполнения, выступлений и, для барда, колдовского фокуса.';
	}
}
