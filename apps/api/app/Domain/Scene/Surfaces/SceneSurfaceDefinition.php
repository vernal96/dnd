<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

/**
 * Описывает серверный контракт поверхности сцены.
 */
interface SceneSurfaceDefinition
{
	/**
	 * Возвращает код поверхности.
	 */
	public function code(): string;

	/**
	 * Возвращает путь картинки поверхности.
	 */
	public function image(): string;

	/**
	 * Возвращает название поверхности.
	 */
	public function name(): string;

	/**
	 * Возвращает признак проходимости поверхности.
	 */
	public function isPassable(): bool;

	/**
	 * Возвращает признак блокировки обзора поверхностью.
	 */
	public function blocksVision(): bool;

	/**
	 * Возвращает теги поверхности.
	 *
	 * @return list<string>
	 */
	public function tags(): array;
}
