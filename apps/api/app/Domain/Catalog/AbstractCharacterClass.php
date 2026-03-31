<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\StartingEquipmentEntryData;

/**
 * Базовая сущность класса персонажа, реализуемая конкретными классами.
 */
abstract class AbstractCharacterClass
{
	/**
	 * Преобразует класс персонажа в ответ API.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     description: ?string,
	 *     isActive: bool,
	 *     subclasses: list<array{code: string, name: string, description: ?string, isActive: bool}>,
	 *     startingEquipment: list<array{
	 *         quantity: int,
	 *         item: array{
	 *             code: string,
	 *             name: string,
	 *             type: string,
	 *             category: string,
	 *             damageDice: ?string,
	 *             versatileDamageDice: ?string,
	 *             attackAbilities: list<string>,
	 *             armorClassBase: ?int,
	 *             armorClassAbility: ?string,
	 *             armorClassAbilityCap: ?int,
	 *             armorClassBonus: ?int,
	 *             description: ?string,
	 *             isActive: bool
	 *         }
	 *     }>
	 * }
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'isActive' => $this->isActive(),
			'subclasses' => array_map(
				static fn(AbstractCharacterSubclass $subclass): array => $subclass->toArray(),
				$this->getActiveSubclasses(),
			),
			'startingEquipment' => array_map(
				static fn(StartingEquipmentEntryData $entry): array => $entry->toArray(),
				$this->getStartingEquipment(),
			),
		];
	}

	/**
	 * Возвращает код класса персонажа.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название класса персонажа.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание класса персонажа.
	 */
	abstract public function getDescription(): ?string;

	/**
	 * Возвращает признак активности класса персонажа.
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Возвращает только активные подклассы текущего класса персонажа.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getActiveSubclasses(): array
	{
		return array_values(array_filter(
			$this->getSubclasses(),
			static fn(AbstractCharacterSubclass $subclass): bool => $subclass->isActive(),
		));
	}

	/**
	 * Возвращает подклассы текущего класса персонажа.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [];
	}

	/**
	 * Возвращает стартовое снаряжение класса персонажа.
	 *
	 * @return list<StartingEquipmentEntryData>
	 */
	public function getStartingEquipment(): array
	{
		return [];
	}

	/**
	 * Создает одну запись стартового снаряжения.
	 */
	protected function makeStartingEquipmentEntry(
		string $itemClass,
		int $quantity = 1,
	): StartingEquipmentEntryData {
		return new StartingEquipmentEntryData(
			itemClass: $itemClass,
			quantity: $quantity,
		);
	}
}
