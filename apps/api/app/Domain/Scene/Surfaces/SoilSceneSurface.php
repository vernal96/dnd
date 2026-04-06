<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Domain\Actor\Elements\ActorElementDefinition;

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

	/**
	 * Возвращает стихию поверхности.
	 */
	public function element(): ?ActorElementDefinition
	{
		return null;
	}

	public function tags(): array
	{
		return ['natural'];
	}
}
