<?php

declare(strict_types=1);

namespace App\Domain\Scene\Objects;

/**
 * Описывает authored-объект бочки.
 */
final class BarrelSceneObject implements SceneObjectDefinition
{

	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string
	{
		return 'barrel';
	}

	public function image(): string
	{
		return 'barrel.png';
	}

	public function name(): string
	{
		return 'Бочка';
	}

	public function width(): int
	{
		return 1;
	}

	public function height(): int
	{
		return 1;
	}

	public function isInteractive(): bool
	{
		return true;
	}

	public function blocksVision(): bool
	{
		return false;
	}

	public function isPassable(): bool
	{
		return false;
	}

	public function tags(): array
	{
		return ['container', 'interior'];
	}
}
