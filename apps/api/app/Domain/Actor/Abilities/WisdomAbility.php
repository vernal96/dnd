<?php

declare(strict_types=1);

namespace App\Domain\Actor\Abilities;

use App\Domain\Actor\Ability;

/**
 * Сущность характеристики мудрости.
 */
final class WisdomAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'wis';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Мудрость';
	}

	/**
	 * Возвращает описание характеристики.
	 */
	public function getDescription(): ?string
	{
		return 'Мудрость отражает внимательность к миру, интуицию, проницательность и здравый смысл.';
	}
}
