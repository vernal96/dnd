<?php

declare(strict_types=1);

namespace App\Data\Game;

use App\Domain\Actor\ActorEffect;
use App\Domain\Actor\Dice;

/**
 * Описывает правило наложения эффекта поверхностью.
 */
final readonly class SurfaceEffectRuleData
{
	/**
	 * Создает правило наложения эффекта поверхностью.
	 */
	public function __construct(
		public ActorEffect $effect,
		public Dice $rollDice,
		public int $applyWhenRollBelow,
		public int $durationTurns,
		public int $durationSeconds,
	)
	{
	}
}
