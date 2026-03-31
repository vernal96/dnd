<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Кираса".
 */
final class BreastplateItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'breastplate';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Кираса';
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
		return 'medium-armor';
	}

	/**
	 * Возвращает базовый КД предмета брони.
	 */
	public function getArmorClassBase(): ?int
	{
		return 14;
	}

	/**
	 * Возвращает характеристику, влияющую на КД брони.
	 */
	public function getArmorClassAbility(): ?Ability
	{
		return new DexterityAbility;
	}

	/**
	 * Возвращает максимальный бонус характеристики к КД брони.
	 */
	public function getArmorClassAbilityCap(): ?int
	{
		return 2;
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Жёсткая металлическая защита груди, сохраняющая подвижность рук и ног.';
	}
}
