<?php

declare(strict_types=1);

namespace App\Domain\Actor\Items;

use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Ability;
use App\Domain\Actor\Dice;
use App\Domain\Actor\Item;
use App\Domain\Actor\ItemType;

/**
 * Сущность предмета "Короткий лук".
 */
final class ShortbowItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'shortbow';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Короткий лук';
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
	public function getDamageDice(): ?Dice
	{
		return Dice::D6;
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
		return 'Легкий дальнобойный лук для быстрых выстрелов на средней дистанции.';
	}
}
