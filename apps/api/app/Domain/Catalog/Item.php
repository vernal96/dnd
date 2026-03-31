<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use JsonSerializable;

/**
 * Базовая сущность предмета кодового каталога.
 */
abstract class Item implements JsonSerializable
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
	public function getDamageDice(): ?WeaponDamageDice
	{
		return null;
	}

	/**
	 * Возвращает альтернативный кубик урона, например при использовании двумя руками.
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
	 * Возвращает признак активности предмета.
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Преобразует предмет в ответ API.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     type: string,
	 *     category: string,
	 *     damageDice: ?string,
	 *     versatileDamageDice: ?string,
	 *     attackAbilities: list<string>,
	 *     armorClassBase: ?int,
	 *     armorClassAbility: ?string,
	 *     armorClassAbilityCap: ?int,
	 *     armorClassBonus: ?int,
	 *     description: ?string,
	 *     isActive: bool
	 * }
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'type' => $this->getType()->value,
			'category' => $this->getCategory(),
			'damageDice' => $this->getDamageDice()?->value,
			'versatileDamageDice' => $this->getVersatileDamageDice()?->value,
			'attackAbilities' => array_map(
				static fn(Ability $ability): string => $ability->getCode(),
				$this->getAttackAbilities(),
			),
			'armorClassBase' => $this->getArmorClassBase(),
			'armorClassAbility' => $this->getArmorClassAbility()?->getCode(),
			'armorClassAbilityCap' => $this->getArmorClassAbilityCap(),
			'armorClassBonus' => $this->getArmorClassBonus(),
			'description' => $this->getDescription(),
			'isActive' => $this->isActive(),
		];
	}

	/**
	 * Возвращает сериализуемое представление предмета.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     type: string,
	 *     category: string,
	 *     damageDice: ?string,
	 *     versatileDamageDice: ?string,
	 *     attackAbilities: list<string>,
	 *     armorClassBase: ?int,
	 *     armorClassAbility: ?string,
	 *     armorClassAbilityCap: ?int,
	 *     armorClassBonus: ?int,
	 *     description: ?string,
	 *     isActive: bool
	 * }
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
