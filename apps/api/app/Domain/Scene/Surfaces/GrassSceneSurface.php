<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

/**
 * Описывает поверхность травы.
 */
final class GrassSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'grass';
	}

	public function image(): string
	{
		return 'grass.png';
	}

	public function name(): string
	{
		return 'Трава';
	}

	public function isPassable(): bool
	{
		return true;
	}

	public function blocksVision(): bool
	{
		return false;
	}

	public function tags(): array
	{
		return ['natural', 'soft'];
	}
}
