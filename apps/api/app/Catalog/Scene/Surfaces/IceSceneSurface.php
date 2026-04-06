<?php

declare(strict_types=1);

namespace App\Catalog\Scene\Surfaces;

/**
 * Описывает поверхность льда.
 */
final class IceSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'ice';
	}

	public function image(): string
	{
		return 'ice.png';
	}

	public function name(): string
	{
		return 'Лед';
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
		return ['slippery', 'cold'];
	}
}
