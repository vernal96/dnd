<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\Elements\ActorElementDefinition;

/**
 * Описывает поверхность камня.
 */
final class StoneSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'stone';
	}

	public function image(): string
	{
		return 'stone.png';
	}

	public function name(): string
	{
		return 'Камень';
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
		return ['solid'];
	}
}
