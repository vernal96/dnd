<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Деревянный щит".
 */
final class WoodenShieldItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'wooden-shield';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Деревянный щит';
	}

	/**
	 * Возвращает тип предмета.
	 */
	public function getType(): ItemType
	{
		return ItemType::Armor;
	}

	/**
	 * Возвращает категорию предмета.
	 */
	public function getCategory(): string
	{
		return 'shields';
	}

	/**
	 * Возвращает фиксированный бонус к КД от предмета.
	 */
	public function getArmorClassBonus(): ?int
	{
		return 2;
	}
}
