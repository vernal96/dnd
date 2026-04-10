<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\ActorEffect;
use App\Domain\Actor\Dice;
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
		return true;
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

	public function effectRules(): array
	{
		return [
			new SurfaceEffectRuleData(
				effect: ActorEffect::Burning,
				rollDice: Dice::D20,
				applyWhenRollBelow: 21,
				durationTurns: 1,
				durationSeconds: 10,
			),
		];
	}

	public function tags(): array
	{
		return ['hazard', 'damage'];
	}
}
