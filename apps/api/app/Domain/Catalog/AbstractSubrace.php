<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\AbilityBonusChoiceData;

/**
 * Базовая сущность подрасы, реализуемая конкретными классами.
 */
abstract class AbstractSubrace
{
	/**
	 * Преобразует подрасу в ответ API.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     description: ?string,
	 *     isActive: bool,
	 *     abilityBonuses: array{str: int, dex: int, con: int, int: int, wis: int, cha: int},
	 *     abilityBonusChoices: list<array{count: int, value: int, abilities: list<string>}>
	 * }
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'isActive' => $this->isActive(),
			'abilityBonuses' => $this->getAbilityBonuses()->toArray(),
			'abilityBonusChoices' => array_map(
				static fn(AbilityBonusChoiceData $choice): array => $choice->toArray(),
				$this->getAbilityBonusChoices(),
			),
		];
	}

	/**
	 * Возвращает код подрасы.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название подрасы.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание подрасы.
	 */
	abstract public function getDescription(): ?string;

	/**
	 * Возвращает признак активности подрасы.
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Возвращает фиксированные бонусы характеристик подрасы.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData;
	}

	/**
	 * Возвращает варианты выбора бонусов характеристик подрасы.
	 *
	 * @return list<AbilityBonusChoiceData>
	 */
	public function getAbilityBonusChoices(): array
	{
		return [];
	}
}
