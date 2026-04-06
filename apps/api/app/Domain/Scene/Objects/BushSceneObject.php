<?php

declare(strict_types=1);

namespace App\Domain\Scene\Objects;

/**
 * Описывает authored-объект куста.
 */
final class BushSceneObject implements SceneObjectDefinition
{

	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string
	{
		return 'bush';
	}

	public function image(): string
	{
		return 'bush.png';
	}

	public function name(): string
	{
		return 'Куст';
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
		return false;
	}

	public function blocksVision(): bool
	{
		return true;
	}

	public function isPassable(): bool
	{
		return false;
	}

	public function tags(): array
	{
		return ['nature', 'cover'];
	}
}
