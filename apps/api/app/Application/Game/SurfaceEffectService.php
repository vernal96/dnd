<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Application\Catalog\RaceCatalog;
use App\Domain\Actor\Dice;
use App\Domain\Actor\LuckScale;
use App\Domain\Actor\Elements\ActorElementDefinition;
use App\Domain\Scene\Surfaces\SceneSurfaceDefinition;
use App\Models\ActorInstance;

/**
 * Применяет эффекты поверхностей к runtime-акторам.
 */
final class SurfaceEffectService
{
	/**
	 * Создает сервис эффектов поверхностей.
	 */
	public function __construct(
		private readonly RaceCatalog $raceCatalog,
		private readonly RandomDiceRollerService $diceRoller,
	)
	{
	}

	/**
	 * Применяет эффект поверхности к актеру после входа на клетку.
	 */
	public function applySurfaceEffect(ActorInstance $actorInstance, SceneSurfaceDefinition $surface): void
	{
		$element = $surface->element();
		$damageDice = $element?->damageDice();

		if ($element === null || $damageDice === null) {
			return;
		}

		$luckScale = LuckScale::tryFrom($actorInstance->luck) ?? LuckScale::Normal;
		$surfaceTriggerRoll = $this->diceRoller->roll(Dice::D20, $luckScale);

		if ($surfaceTriggerRoll <= (int) floor(Dice::D20->value / 2)) {
			return;
		}

		$rolledDamage = $this->diceRoller->roll($damageDice, $luckScale);
		$resistancePercent = $this->resolveActorResistancePercent($actorInstance, $element);
		$damage = (int) max(
			0,
			round($rolledDamage - ($rolledDamage * $resistancePercent / 100), 0, PHP_ROUND_HALF_UP),
		);

		if ($damage <= 0 || !is_int($actorInstance->hp_current)) {
			return;
		}

		$actorInstance->forceFill([
			'hp_current' => max(0, $actorInstance->hp_current - $damage),
		])->save();
	}

	/**
	 * Возвращает сопротивление актора по коду стихии.
	 */
	private function resolveActorResistancePercent(ActorInstance $actorInstance, ActorElementDefinition $element): int
	{
		$raceCode = $actorInstance->runtime_state['race'] ?? null;

		if (!is_string($raceCode) || $raceCode === '') {
			return 0;
		}

		$race = $this->raceCatalog->findActiveRaceByCode($raceCode);

		if ($race === null) {
			return 0;
		}

		return $race->getElementResistances()->getPercentForElement($element);
	}
}
