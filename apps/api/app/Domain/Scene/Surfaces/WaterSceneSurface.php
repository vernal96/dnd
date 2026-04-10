<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\Elements\ActorElementDefinition;

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
		return ['liquid', 'hazard'];
	}
}
