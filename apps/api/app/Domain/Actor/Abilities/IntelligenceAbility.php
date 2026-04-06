<?php

declare(strict_types=1);

namespace App\Domain\Actor\Abilities;

use App\Domain\Actor\Ability;

/**
 * Сущность характеристики интеллекта.
 */
final class IntelligenceAbility extends Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	public function getCode(): string
	{
		return 'int';
	}

	/**
	 * Возвращает название характеристики.
	 */
	public function getName(): string
	{
		return 'Интеллект';
	}

	/**
	 * Возвращает описание характеристики.
	 */
	public function getDescription(): ?string
	{
		return 'Интеллект отражает память, рассудочность, эрудицию и способность к анализу.';
	}
}
