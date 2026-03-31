<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\AbilityBonusesData;
use App\Data\Catalog\AbilityBonusChoiceData;

/**
 * Базовая сущность расы, реализуемая конкретными классами.
 */
abstract class AbstractRace
{
	/**
	 * Преобразует расу в ответ API.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     description: ?string,
	 *     isActive: bool,
	 *     abilityBonuses: array{str: int, dex: int, con: int, int: int, wis: int, cha: int},
	 *     abilityBonusChoices: list<array{count: int, value: int, abilities: list<string>}>,
	 *     subraces: list<array{
	 *         code: string,
	 *         name: string,
	 *         description: ?string,
	 *         isActive: bool,
	 *         abilityBonuses: array{str: int, dex: int, con: int, int: int, wis: int, cha: int},
	 *         abilityBonusChoices: list<array{count: int, value: int, abilities: list<string>}>
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
			'abilityBonuses' => $this->getAbilityBonuses()->toArray(),
			'abilityBonusChoices' => array_map(
				static fn(AbilityBonusChoiceData $choice): array => $choice->toArray(),
				$this->getAbilityBonusChoices(),
			),
			'subraces' => array_map(
				static fn(AbstractSubrace $subrace): array => $subrace->toArray(),
				$this->getActiveSubraces(),
			),
		];
	}

	/**
	 * Возвращает код расы.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название расы.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание расы.
	 */
	abstract public function getDescription(): ?string;

	/**
	 * Возвращает признак активности расы.
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Возвращает фиксированные бонусы характеристик расы.
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData;
	}

	/**
	 * Возвращает варианты выбора бонусов характеристик расы.
	 *
	 * @return list<AbilityBonusChoiceData>
	 */
	public function getAbilityBonusChoices(): array
	{
		return [];
	}

	/**
	 * Возвращает только активные подрасы текущей расы.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getActiveSubraces(): array
	{
		return array_values(array_filter(
			$this->getSubraces(),
			static fn(AbstractSubrace $subrace): bool => $subrace->isActive(),
		));
	}

	/**
	 * Возвращает подрасы текущей расы.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [];
	}
}
