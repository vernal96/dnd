<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\Elements\ActorElementDefinition;

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

	/**
	 * Возвращает стихию поверхности.
	 */
	public function element(): ?ActorElementDefinition
	{
		return null;
	}

	public function effectRules(): array
	{
		return [];
	}

	public function tags(): array
	{
		return ['natural', 'soft'];
	}
}
