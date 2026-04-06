<?php

declare(strict_types=1);

namespace App\Catalog\Scene\Surfaces;

/**
 * Описывает поверхность земли.
 */
final class SoilSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'soil';
	}

	public function image(): string
	{
		return 'soil.png';
	}

	public function name(): string
	{
		return 'Земля';
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
		return ['natural'];
	}
}
