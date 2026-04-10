<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Application\Catalog\RaceCatalog;
use App\Domain\Actor\ActorEffect;
use App\Domain\Actor\Dice;
use App\Domain\Actor\Elements\ActorElementDefinition;
use App\Domain\Actor\LuckScale;
use App\Domain\Scene\Surfaces\SceneSurfaceDefinition;
use App\Models\ActorInstance;
use Illuminate\Support\Carbon;

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
		$this->purgeExpiredEffects($actorInstance);

		$element = $surface->element();
		$damageDice = $element?->damageDice();

		if ($element === null || $damageDice === null) {
			$this->applySurfaceStatusEffects($actorInstance, $surface);

			return;
		}

		$luckScale = LuckScale::tryFrom($actorInstance->luck) ?? LuckScale::Normal;
		$surfaceTriggerRoll = $this->diceRoller->roll(Dice::D20, $luckScale);

		if ($surfaceTriggerRoll <= (int) floor(Dice::D20->value / 2)) {
			$this->applySurfaceStatusEffects($actorInstance, $surface);

			return;
		}

		$rolledDamage = $this->diceRoller->roll($damageDice, $luckScale);
		$resistancePercent = $this->resolveActorResistancePercent($actorInstance, $element);
		$damage = (int) max(
			0,
			round($rolledDamage - ($rolledDamage * $resistancePercent / 100), 0, PHP_ROUND_HALF_UP),
		);

		if ($damage <= 0 || !is_int($actorInstance->hp_current)) {
			$this->applySurfaceStatusEffects($actorInstance, $surface);

			return;
		}

		$actorInstance->forceFill([
			'hp_current' => max(0, $actorInstance->hp_current - $damage),
		])->save();

		$this->applySurfaceStatusEffects($actorInstance, $surface);
	}

	/**
	 * Возвращает активные эффекты runtime-актора.
	 *
	 * @return list<array<string, mixed>>
	 */
	public function activeEffects(ActorInstance $actorInstance): array
	{
		$effects = $this->purgeExpiredEffects($actorInstance);

		return array_values(array_filter(
			$effects,
			static fn (array $effect): bool => ActorEffect::tryFrom((string) ($effect['code'] ?? '')) instanceof ActorEffect,
		));
	}

	/**
	 * Проверяет, есть ли у актора активный эффект.
	 */
	public function hasActiveEffect(ActorInstance $actorInstance, ActorEffect $effect): bool
	{
		foreach ($this->activeEffects($actorInstance) as $activeEffect) {
			if (($activeEffect['code'] ?? null) === $effect->value) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Возвращает скорость актора с учетом активных эффектов.
	 */
	public function resolveEffectiveMovementSpeed(ActorInstance $actorInstance): int
	{
		if ($this->hasActiveEffect($actorInstance, ActorEffect::Prone)) {
			return 0;
		}

		$movementSpeed = (int) ($actorInstance->movement_speed ?? 0);

		foreach ($this->activeEffects($actorInstance) as $effectPayload) {
			$effect = ActorEffect::tryFrom((string) ($effectPayload['code'] ?? ''));

			if ($effect instanceof ActorEffect) {
				$movementSpeed -= $effect->movementPenalty();
			}
		}

		return max(0, $movementSpeed);
	}

	/**
	 * Списывает один боевой ход у эффекта и возвращает true, если ход должен быть пропущен.
	 */
	public function consumeSkippedTurnIfNeeded(ActorInstance $actorInstance): bool
	{
		if (!$this->hasActiveEffect($actorInstance, ActorEffect::Prone)) {
			return false;
		}

		$this->consumeCombatTurnDuration($actorInstance, ActorEffect::Prone);

		return true;
	}

	/**
	 * Применяет status-эффекты поверхности к актеру.
	 */
	private function applySurfaceStatusEffects(ActorInstance $actorInstance, SceneSurfaceDefinition $surface): void
	{
		$rules = $surface->effectRules();

		if ($rules === []) {
			return;
		}

		$luckScale = LuckScale::tryFrom($actorInstance->luck) ?? LuckScale::Normal;
		$effects = $this->purgeExpiredEffects($actorInstance);
		$changed = false;

		foreach ($rules as $rule) {
			$roll = $this->diceRoller->roll($rule->rollDice, $luckScale);

			if ($roll >= $rule->applyWhenRollBelow) {
				continue;
			}

			$effects = $this->upsertEffect($effects, $rule->effect, $surface, $rule->durationTurns, $rule->durationSeconds);
			$changed = true;
		}

		if (!$changed) {
			return;
		}

		$actorInstance->forceFill([
			'temporary_effects' => $effects,
		])->save();
	}

	/**
	 * Добавляет или обновляет эффект в списке эффектов.
	 *
	 * @param list<array<string, mixed>> $effects
	 * @return list<array<string, mixed>>
	 */
	private function upsertEffect(array $effects, ActorEffect $effect, SceneSurfaceDefinition $surface, int $durationTurns, int $durationSeconds): array
	{
		$payload = [
			'code' => $effect->value,
			'label' => $effect->label(),
			'type' => $effect->type(),
			'icon' => $effect->icon(),
			'source_type' => 'surface',
			'source_code' => $surface->code(),
			'remaining_turns' => max(0, $durationTurns),
			'expires_at' => Carbon::now()->addSeconds(max(1, $durationSeconds))->toJSON(),
		];

		foreach ($effects as $index => $activeEffect) {
			if (($activeEffect['code'] ?? null) === $effect->value) {
				$effects[$index] = $payload;

				return $effects;
			}
		}

		$effects[] = $payload;

		return $effects;
	}

	/**
	 * Удаляет истекшие вне боя эффекты.
	 *
	 * @return list<array<string, mixed>>
	 */
	private function purgeExpiredEffects(ActorInstance $actorInstance): array
	{
		$effects = is_array($actorInstance->temporary_effects) ? $actorInstance->temporary_effects : [];
		$now = Carbon::now();
		$activeEffects = [];

		foreach ($effects as $effect) {
			if (!is_array($effect)) {
				continue;
			}

			$expiresAt = is_string($effect['expires_at'] ?? null)
				? Carbon::parse($effect['expires_at'])
				: null;

			if ($expiresAt instanceof Carbon && $expiresAt->lessThanOrEqualTo($now)) {
				continue;
			}

			$activeEffects[] = $effect;
		}

		if (count($activeEffects) !== count($effects)) {
			$actorInstance->forceFill([
				'temporary_effects' => $activeEffects,
			])->save();
		}

		return $activeEffects;
	}

	/**
	 * Уменьшает боевую длительность эффекта на один ход.
	 */
	private function consumeCombatTurnDuration(ActorInstance $actorInstance, ActorEffect $effect): void
	{
		$effects = $this->purgeExpiredEffects($actorInstance);
		$nextEffects = [];

		foreach ($effects as $activeEffect) {
			if (($activeEffect['code'] ?? null) !== $effect->value) {
				$nextEffects[] = $activeEffect;
				continue;
			}

			$remainingTurns = max(0, (int) ($activeEffect['remaining_turns'] ?? 0) - 1);

			if ($remainingTurns > 0) {
				$activeEffect['remaining_turns'] = $remainingTurns;
				$nextEffects[] = $activeEffect;
			}
		}

		$actorInstance->forceFill([
			'temporary_effects' => $nextEffects,
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
