<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\CharacterClassSkillProgressionData;
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
	 *     skillsByLevel: array{
	 *         level1: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level2: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level3: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level4: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level5: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level6: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level7: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level8: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level9: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level10: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level11: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level12: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level13: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level14: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level15: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level16: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level17: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level18: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level19: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *         level20: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>
	 *     },
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
			'skillsByLevel' => $this->getSkillsByLevel()->toArray(),
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
	 * Возвращает распределение навыков класса персонажа по уровням.
	 */
	public function getSkillsByLevel(): CharacterClassSkillProgressionData
	{
		return new CharacterClassSkillProgressionData;
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
