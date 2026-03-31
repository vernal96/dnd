<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Бумага / пергамент".
 */
final class PaperParchmentItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'paper-parchment';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Бумага / пергамент';
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
		return 'useful-trinkets';
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Листы бумаги или пергамента для записей, карт, переписки и заметок.';
	}
}
