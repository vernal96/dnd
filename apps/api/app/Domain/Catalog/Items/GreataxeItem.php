<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Items;

use App\Domain\Catalog\Abilities\StrengthAbility;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use App\Domain\Catalog\ItemType;
use App\Domain\Catalog\WeaponDamageDice;

/**
 * Сущность предмета "Большой топор".
 */
final class GreataxeItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'greataxe';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Большой топор';
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
		return WeaponDamageDice::D12;
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
			new StrengthAbility,
		];
	}
	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return 'Тяжёлый двуручный топор, рассчитанный на редкие, но особенно разрушительные удары.';
	}
}
