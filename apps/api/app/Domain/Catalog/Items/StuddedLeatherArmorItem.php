<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;

/**
 * Сущность предмета "Проклёпанная кожаная".
 */
final class StuddedLeatherArmorItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'studded-leather-armor';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Проклёпанная кожаная';
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
		return 'light-armor';
	}

	/**
	 * Возвращает базовый КД предмета брони.
	 */
	public function getArmorClassBase(): ?int
	{
		return 12;
	}

	/**
	 * Возвращает характеристику, влияющую на КД брони.
	 */
	public function getArmorClassAbility(): ?Ability
	{
		return new DexterityAbility;
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Усиленная кожаная броня с плотной посадкой заклёпок или шипов для лучшей защиты.';
	}
}
