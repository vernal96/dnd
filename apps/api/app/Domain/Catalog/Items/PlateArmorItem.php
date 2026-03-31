<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Латы".
 */
final class PlateArmorItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'plate-armor';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Латы';
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
		return 'heavy-armor';
	}

	/**
	 * Возвращает базовый КД предмета брони.
	 */
	public function getArmorClassBase(): ?int
	{
		return 18;
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Полный латный доспех из сочленённых пластин, обеспечивающий лучшую обычную защиту среди немагической брони.';
	}
}
