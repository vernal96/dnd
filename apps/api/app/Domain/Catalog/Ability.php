<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность игровой характеристики персонажа.
 */
abstract class Ability
{
	/**
	 * Возвращает код характеристики.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название характеристики.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание характеристики.
	 */
	public function getDescription(): ?string
	{
		return null;
	}
}
