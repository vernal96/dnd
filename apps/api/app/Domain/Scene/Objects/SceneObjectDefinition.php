<?php

declare(strict_types=1);

namespace App\Domain\Scene\Objects;

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
	 * Возвращает путь картинки.
	 */
	public function image(): string;

	/**
	 * Возвращает название authored-объекта.
	 */
	public function name(): string;

	/**
	 * Возвращает ширину authored-объекта.
	 */
	public function width(): int;

	/**
	 * Возвращает высоту authored-объекта.
	 */
	public function height(): int;

	/**
	 * Возвращает признак интерактивности authored-объекта.
	 */
	public function isInteractive(): bool;

	/**
	 * Возвращает признак блокировки обзора authored-объектом.
	 */
	public function blocksVision(): bool;

	/**
	 * Возвращает признак проходимости authored-объекта.
	 */
	public function isPassable(): bool;

	/**
	 * Возвращает теги authored-объекта.
	 *
	 * @return list<string>
	 */
	public function tags(): array;
}
