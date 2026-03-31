<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Catalog\Item;

/**
 * Хранит одну запись стартового снаряжения класса персонажа.
 */
final readonly class StartingEquipmentEntryData
{
	/**
	 * Создает DTO записи стартового снаряжения.
	 */
	public function __construct(
		public string $itemClass,
		public int $quantity = 1,
	)
	{
	}

	/**
	 * Возвращает экземпляр предмета записи.
	 */
	public function getItem(): Item
	{
		$itemClass = $this->itemClass;

		return new $itemClass;
	}

	/**
	 * Преобразует DTO в массив для API.
	 *
	 * @return array{
	 *     quantity: int,
	 *     item: array{
	 *         code: string,
	 *         name: string,
	 *         type: string,
	 *         category: string,
	 *         damageDice: ?string,
	 *         versatileDamageDice: ?string,
	 *         attackAbilities: list<string>,
	 *         armorClassBase: ?int,
	 *         armorClassAbility: ?string,
	 *         armorClassAbilityCap: ?int,
	 *         armorClassBonus: ?int,
	 *         description: ?string,
	 *         isActive: bool
	 *     }
	 * }
	 */
	public function toArray(): array
	{
		return [
			'quantity' => $this->quantity,
			'item' => $this->getItem()->toArray(),
		];
	}
}
