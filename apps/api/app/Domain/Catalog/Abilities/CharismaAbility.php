<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Abilities;

use App\Domain\Catalog\Ability;

/**
 * Сущность характеристики харизмы.
 */
final class CharismaAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'cha';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Харизма';
	}

	/**
	 * Возвращает описание характеристики.
	 */
	public function getDescription(): ?string
	{
		return 'Харизма отражает силу личности, уверенность, обаяние и умение влиять на других.';
	}
}
