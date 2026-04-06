<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Domain\Actor\Elements\ActorElementDefinition;
use App\Domain\Actor\Elements\FireElement;

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

	/**
	 * Возвращает стихию поверхности.
	 */
	public function element(): ActorElementDefinition
	{
		return new FireElement();
	}

	public function tags(): array
	{
		return ['hazard', 'damage'];
	}
}
