<?php

declare(strict_types=1);

namespace App\Domain\Actor\Items;

use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\Ability;
use App\Domain\Actor\Item;
use App\Domain\Actor\ItemType;
use App\Domain\Actor\Dice;

/**
 * Сущность предмета "Длинный меч".
 */
final class LongswordItem extends Item
{
	/**
	 * Возвращает код предмета.
	 */
	public function getCode(): string
	{
		return 'longsword';
	}

	/**
	 * Возвращает название предмета.
	 */
	public function getName(): string
	{
		return 'Длинный меч';
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
	public function getDamageDice(): ?Dice
	{
		return Dice::D8;
	}

	/**
	 * Возвращает альтернативный кубик урона оружия.
	 */
	public function getVersatileDamageDice(): ?Dice
	{
		return Dice::D10;
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
		return 'Классический длинный меч, одинаково надёжный со щитом и при хвате двумя руками.';
	}
}
