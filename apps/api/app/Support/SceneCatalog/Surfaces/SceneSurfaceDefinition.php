<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

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
	 * Возвращает серверное представление поверхности.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
}
