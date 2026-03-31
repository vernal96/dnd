<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Abilities\DexterityAbility;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;
use App\Domain\Catalog\WeaponDamageDice;

/**
 * Сущность предмета "Лёгкий арбалет".
 */
final class LightCrossbowItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'light-crossbow';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Лёгкий арбалет';
	}

	/**
	 * Возвращает тип предмета.
	 */
	public function getType(): ItemType
	{
		return ItemType::RangedWeapon;
	}

	/**
	 * Возвращает категорию предмета.
	 */
	public function getCategory(): string
	{
		return 'simple-ranged-weapon';
	}

	/**
	 * Возвращает основной кубик урона оружия.
	 */
	public function getDamageDice(): ?WeaponDamageDice
	{
		return WeaponDamageDice::D8;
	}

	/**
	 * Возвращает альтернативный кубик урона оружия.
	 */
	public function getVersatileDamageDice(): ?WeaponDamageDice
	{
		return null;
	}

	/**
	 * Возвращает характеристики, влияющие на бросок оружия.
	 *
	 * @return list<Ability>
	 */
	public function getAttackAbilities(): array
	{
		return [
			new DexterityAbility,
		];
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Простой арбалет с хорошей дальностью и надёжной точностью. Требует обеих рук и времени на перезарядку.';
	}
}
