<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

/**
 * Описывает поверхность огня.
 */
final class FireSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'fire';
	}

	public function image(): string
	{
		return 'fire.png';
	}

	public function name(): string
	{
		return 'Огонь';
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
		return ['hazard', 'damage'];
	}
}
