<?php

declare(strict_types=1);

namespace App\Catalog\Scene\Surfaces;

/**
 * Описывает поверхность воды.
 */
final class WaterSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'water';
	}

	public function image(): string
	{
		return 'water.png';
	}

	public function name(): string
	{
		return 'Вода';
	}

	public function isPassable(): bool
	{
		return false;
	}

	public function blocksVision(): bool
	{
		return false;
	}

	public function tags(): array
	{
		return ['liquid', 'hazard'];
	}
}
