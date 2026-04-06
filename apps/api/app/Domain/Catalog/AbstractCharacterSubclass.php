<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность подкласса персонажа, реализуемая конкретными классами.
 */
abstract class AbstractCharacterSubclass
{
	protected const string CODE = '';
	protected const string NAME = '';
	protected const ?string DESCRIPTION = null;

	/**
	 * Возвращает код подкласса персонажа.
	 */
	final public function getCode(): string
	{
		return static::CODE;
	}

	/**
	 * Возвращает название подкласса персонажа.
	 */
	final public function getName(): string
	{
		return static::NAME;
	}

	/**
	 * Возвращает описание подкласса персонажа.
	 */
	final public function getDescription(): ?string
	{
		return static::DESCRIPTION;
	}

	/**
	 * Возвращает признак активности подкласса персонажа.
	 */
	public function isActive(): bool
	{
		return true;
	}
}
