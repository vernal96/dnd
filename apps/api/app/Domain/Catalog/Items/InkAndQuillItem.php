<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Чернила и перо".
 */
final class InkAndQuillItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'ink-and-quill';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Чернила и перо';
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
}
