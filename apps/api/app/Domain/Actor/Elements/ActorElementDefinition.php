<?php

declare(strict_types=1);

namespace App\Domain\Actor\Elements;

use App\Domain\Actor\Dice;

/**
 * Описывает контракт стихийного эффекта для поверхностей и сопротивлений.
 */
interface ActorElementDefinition
{
	/**
	 * Возвращает код стихии.
	 */
	public function code(): string;

	/**
	 * Возвращает кубик урона стихии или null, если стихия не наносит урон.
	 */
	public function damageDice(): ?Dice;
}
