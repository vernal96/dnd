<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Domain\Actor\Elements\ActorElementDefinition;

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

	/**
	 * Возвращает стихию поверхности.
	 */
	public function element(): ?ActorElementDefinition
	{
		return null;
	}

	public function tags(): array
	{
		return ['slippery', 'cold'];
	}
}
