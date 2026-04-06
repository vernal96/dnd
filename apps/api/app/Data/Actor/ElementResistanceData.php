<?php

declare(strict_types=1);

namespace App\Data\Actor;

use App\Domain\Actor\Elements\ActorElementDefinition;

/**
 * Описывает сопротивления сущности ко всем поддерживаемым стихиям.
 */
final readonly class ElementResistanceData
{
	/**
	 * Создает DTO сопротивлений стихиям.
	 */
	public function __construct(
		public int $firePercent = 0,
		public int $poisonPercent = 0,
	)
	{
	}

	/**
	 * Возвращает сопротивление к указанной стихии в процентах.
	 */
	public function getPercentForElement(ActorElementDefinition $element): int
	{
		return match ($element->code()) {
			'fire' => $this->firePercent,
			'poison' => $this->poisonPercent,
			default => 0,
		};
	}
}
