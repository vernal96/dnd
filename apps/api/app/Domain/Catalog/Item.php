<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность предмета кодового каталога.
 */
abstract class Item
{
	/**
	 * Возвращает код предмета.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название предмета.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает тип предмета.
	 */
	abstract public function getType(): ItemType;

	/**
	 * Возвращает категорию предмета.
	 */
	abstract public function getCategory(): string;

	/**
	 * Возвращает основной кубик урона оружия.
	 */
	public function getDamageDice(): ?Dice
	{
		return null;
	}

	/**
	 * Возвращает альтернативный кубик урона, например при использовании двумя руками.
	 */
	public function getVersatileDamageDice(): ?Dice
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
		return [];
	}

	/**
	 * Возвращает базовый КД предмета брони.
	 */
	public function getArmorClassBase(): ?int
	{
		return null;
	}

	/**
	 * Возвращает характеристику, влияющую на КД брони.
	 */
	public function getArmorClassAbility(): ?Ability
	{
		return null;
	}

	/**
	 * Возвращает максимальный бонус характеристики к КД брони.
	 */
	public function getArmorClassAbilityCap(): ?int
	{
		return null;
	}

	/**
	 * Возвращает фиксированный бонус к КД от предмета, например от щита.
	 */
	public function getArmorClassBonus(): ?int
	{
		return null;
	}

	/**
	 * Возвращает описание предмета.
	 */
	public function getDescription(): ?string
	{
		return null;
	}

	/**
	 * Возвращает имя файла изображения предмета в служебном каталоге.
	 */
	public function image(): ?string
	{
		return $this->getCode() . '.png';
	}

	/**
	 * Возвращает признак активности предмета.
	 */
	public function isActive(): bool
	{
		return true;
	}
}
