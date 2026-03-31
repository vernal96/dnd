<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность подрасы, реализуемая конкретными классами.
 */
abstract class AbstractSubrace
{
	/**
	 * Преобразует подрасу в ответ API.
	 *
	 * @return array{code: string, name: string, description: ?string, isActive: bool}
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'isActive' => $this->isActive(),
		];
	}

	/**
	 * Возвращает код подрасы.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название подрасы.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание подрасы.
	 */
	abstract public function getDescription(): ?string;

	/**
	 * Возвращает признак активности подрасы.
	 */
	public function isActive(): bool
	{
		return true;
	}
}
