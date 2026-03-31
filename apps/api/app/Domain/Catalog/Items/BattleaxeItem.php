<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;
use App\Domain\Catalog\WeaponDamageDice;

/**
 * Сущность предмета "Боевой топор".
 */
final class BattleaxeItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'battleaxe';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Боевой топор';
	}

	/**
	 * Возвращает тип предмета.
	 */
	public function getType(): ItemType
	{
		return ItemType::MeleeWeapon;
	}

	/**
	 * Возвращает категорию предмета.
	 */
	public function getCategory(): string
	{
		return 'martial-melee-weapon';
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
		return WeaponDamageDice::D10;
	}

	/**
	 * Возвращает характеристики, влияющие на бросок оружия.
	 *
	 * @return list<Ability>
	 */
	public function getAttackAbilities(): array
	{
		return [
			new StrengthAbility,
		];
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Воинский топор, пригодный для боя одной рукой со щитом или двумя руками ради более сильного удара.';
	}
}
