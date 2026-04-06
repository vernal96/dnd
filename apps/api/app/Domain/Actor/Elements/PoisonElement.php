<?php

declare(strict_types=1);

namespace App\Domain\Actor\Elements;

use App\Domain\Actor\Dice;

/**
 * Описывает стихию яда.
 */
final class PoisonElement implements ActorElementDefinition
{
	/**
	 * Возвращает код стихии.
	 */
	public function code(): string
	{
		return 'poison';
	}

	/**
	 * Возвращает кубик урона стихии.
	 */
	public function damageDice(): Dice
	{
		return Dice::D4;
	}
}
