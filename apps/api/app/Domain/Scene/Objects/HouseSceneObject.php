<?php

declare(strict_types=1);

namespace App\Domain\Scene\Objects;

/**
 * Описывает authored-объект дома.
 */
final class HouseSceneObject implements SceneObjectDefinition
{

	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string
	{
		return 'house';
	}

	/**
	 * Возвращает путь картинки.
	 */
	public function image(): string
	{
		return 'house.png';
	}

	/**
	 * Возвращает название authored-объекта.
	 */
	public function name(): string
	{
		return 'Дом';
	}

	/**
	 * Возвращает ширину authored-объекта.
	 */
	public function width(): int
	{
		return 2;
	}

	/**
	 * Возвращает высоту authored-объекта.
	 */
	public function height(): int
	{
		return 2;
	}

	/**
	 * Возвращает признак интерактивности authored-объекта.
	 */
	public function isInteractive(): bool
	{
		return true;
	}

	/**
	 * Возвращает признак блокировки обзора authored-объектом.
	 */
	public function blocksVision(): bool
	{
		return true;
	}

	/**
	 * Возвращает признак проходимости authored-объекта.
	 */
	public function isPassable(): bool
	{
		return false;
	}

	/**
	 * Возвращает теги authored-объекта.
	 *
	 * @return list<string>
	 */
	public function tags(): array
	{
		return ['building', 'interior', 'container'];
	}
}
