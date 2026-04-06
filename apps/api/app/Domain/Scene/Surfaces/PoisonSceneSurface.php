<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

/**
 * Описывает поверхность яда.
 */
final class PoisonSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'poison';
	}

	public function image(): string
	{
		return 'poison.png';
	}

	public function name(): string
	{
		return 'Яд';
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
		return ['hazard', 'toxic'];
	}
}
