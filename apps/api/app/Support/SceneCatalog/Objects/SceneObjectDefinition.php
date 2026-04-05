<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Objects;

/**
 * Описывает серверный контракт authored-объекта сцены.
 */
interface SceneObjectDefinition
{
	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string;

	/**
	 * Возвращает серверное представление authored-объекта.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
}
