<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Abilities;

use App\Domain\Catalog\Ability;

/**
 * Сущность характеристики телосложения.
 */
final class ConstitutionAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'con';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Телосложение';
	}
}
