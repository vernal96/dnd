<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Abilities;

use App\Domain\Catalog\Ability;

/**
 * Сущность характеристики ловкости.
 */
final class DexterityAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'dex';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Ловкость';
	}

	/**
	 * Возвращает описание характеристики.
	 */
	public function getDescription(): ?string
	{
		return 'Ловкость отражает подвижность, рефлексы, координацию и чувство равновесия.';
	}
}
