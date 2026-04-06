<?php

declare(strict_types=1);

namespace App\Domain\Actor\Elements;

use App\Domain\Actor\Dice;

/**
 * Описывает стихию огня.
 */
final class FireElement implements ActorElementDefinition
{
	/**
	 * Возвращает код стихии.
	 */
	public function code(): string
	{
		return 'fire';
	}

	/**
	 * Возвращает кубик урона стихии.
	 */
	public function damageDice(): Dice
	{
		return Dice::D6;
	}
}
