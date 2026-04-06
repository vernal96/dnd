<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\AbilityBonusesData;

/**
 * Базовая сущность подрасы, реализуемая конкретными классами.
 */
abstract class AbstractSubrace
{
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
