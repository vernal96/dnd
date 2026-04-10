<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\ActorEffect;
use App\Domain\Actor\Dice;
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

	public function effectRules(): array
	{
		return [
			new SurfaceEffectRuleData(
				effect: ActorEffect::Slowed,
				rollDice: Dice::D20,
				applyWhenRollBelow: 21,
				durationTurns: 1,
				durationSeconds: 10,
			),
		];
	}

	public function tags(): array
	{
		return ['natural'];
	}
}
