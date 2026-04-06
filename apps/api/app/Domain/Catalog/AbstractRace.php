<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Data\Catalog\AbilityBonusesData;

/**
 * Базовая сущность расы, реализуемая конкретными классами.
 */
abstract class AbstractRace
{
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
	 * Возвращает признак доступности расы для выбора игроком.
	 */
	public function canBeSelectedByPlayer(): bool
	{
		return false;
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
	 * Возвращает бонус расы к скорости персонажа.
	 */
	public function getSpeedBonus(): int
	{
		return 0;
	}

	/**
	 * Возвращает бонус расы к здоровью персонажа.
	 */
	public function getHealthBonus(): int
	{
		return 0;
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
