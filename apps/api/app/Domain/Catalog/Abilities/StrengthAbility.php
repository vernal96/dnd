<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Abilities;

use App\Domain\Catalog\Ability;

/**
 * Сущность характеристики силы.
 */
final class StrengthAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'str';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Сила';
	}
}
