<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Перечисляет runtime-эффекты, которые могут быть наложены на актора.
 */
enum ActorEffect: string
{
	case Burning = 'burning';
	case Poisoned = 'poisoned';
	case Prone = 'prone';
	case Slowed = 'slowed';

	/**
	 * Возвращает человекочитаемое название эффекта.
	 */
	public function label(): string
	{
		return match ($this) {
			self::Burning => 'Горение',
			self::Poisoned => 'Отравление',
			self::Prone => 'Упал',
			self::Slowed => 'Замедление',
		};
	}

	/**
	 * Возвращает тип эффекта для визуального разделения.
	 */
	public function type(): string
	{
		return 'negative';
	}

	/**
	 * Возвращает короткую иконку эффекта для frontend.
	 */
	public function icon(): string
	{
		return match ($this) {
			self::Burning => '🔥',
			self::Poisoned => '☠',
			self::Prone => '↓',
			self::Slowed => '−',
		};
	}

	/**
	 * Возвращает признак блокировки перемещения эффектом.
	 */
	public function blocksMovement(): bool
	{
		return $this === self::Prone;
	}

	/**
	 * Возвращает штраф к скорости перемещения.
	 */
	public function movementPenalty(): int
	{
		return $this === self::Slowed ? 1 : 0;
	}
}
