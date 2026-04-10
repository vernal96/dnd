<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\StrengthAbility;

/**
 * Рассчитывает производные боевые показатели акторов.
 */
final class ActorCombatStatsService
{
	private const int BASE_HEALTH = 5;
	private const int BASE_MOVEMENT_SPEED = 3;
	private const int BASE_ARMOR_CLASS = 10;
	private const int BASE_JUMP_HEIGHT = 1;

	/**
	 * Возвращает модификатор характеристики.
	 */
	public function resolveAbilityModifier(int $abilityValue): int
	{
		return (int) floor(($abilityValue - 10) / 2);
	}

	/**
	 * Рассчитывает полные производные показатели.
	 *
	 * @param array<string, mixed>|null $stats
	 * @return array{health:int,movement_speed:int,armor_class:int,jump_height:int,damage_bonus:int,modifiers:array{str:int,dex:int,con:int}}
	 */
	public function buildDerivedStats(?array $stats, int $level, int $healthBonus = 0, int $speedBonus = 0, int $armorBonus = 0): array
	{
		$normalizedLevel = $this->normalizeLevel($level);
		$strengthModifier = $this->resolveAbilityModifier($this->resolveAbilityValue($stats, (new StrengthAbility)->getCode()));
		$dexterityModifier = $this->resolveAbilityModifier($this->resolveAbilityValue($stats, (new DexterityAbility)->getCode()));
		$constitutionModifier = $this->resolveAbilityModifier($this->resolveAbilityValue($stats, (new ConstitutionAbility)->getCode()));

		return [
			'health' => max(
				1,
				self::BASE_HEALTH
				+ $healthBonus
				+ (($normalizedLevel - 1) * 4)
				+ ($constitutionModifier * $normalizedLevel)
				+ $strengthModifier,
			),
			'movement_speed' => max(
				2,
				min(6, self::BASE_MOVEMENT_SPEED + $speedBonus + $dexterityModifier + (int) floor($strengthModifier / 2)),
			),
			'armor_class' => self::BASE_ARMOR_CLASS + $armorBonus + max(0, min(3, $dexterityModifier)),
			'jump_height' => max(
				1,
				min(4, self::BASE_JUMP_HEIGHT + (int) floor($strengthModifier / 2) + max(0, (int) floor($dexterityModifier / 2))),
			),
			'damage_bonus' => $this->resolveDamageBonus($strengthModifier, $normalizedLevel),
			'modifiers' => [
				'str' => $strengthModifier,
				'dex' => $dexterityModifier,
				'con' => $constitutionModifier,
			],
		];
	}

	/**
	 * Рассчитывает бонус к урону от основной характеристики и уровня.
	 */
	public function resolveDamageBonus(int $primaryAbilityModifier, int $level): int
	{
		return max(0, min(4, (int) floor($primaryAbilityModifier / 2) + (int) floor(($this->normalizeLevel($level) - 1) / 4)));
	}

	/**
	 * Возвращает базовое здоровье актора.
	 */
	public function baseHealth(): int
	{
		return self::BASE_HEALTH;
	}

	/**
	 * Возвращает базовую скорость актора.
	 */
	public function baseMovementSpeed(): int
	{
		return self::BASE_MOVEMENT_SPEED;
	}

	/**
	 * Возвращает базовый класс брони актора.
	 */
	public function baseArmorClass(): int
	{
		return self::BASE_ARMOR_CLASS;
	}

	/**
	 * Возвращает базовую высоту прыжка актора.
	 */
	public function baseJumpHeight(): int
	{
		return self::BASE_JUMP_HEIGHT;
	}

	/**
	 * Возвращает значение характеристики из payload.
	 *
	 * @param array<string, mixed>|null $stats
	 */
	private function resolveAbilityValue(?array $stats, string $abilityCode): int
	{
		$value = $stats[$abilityCode] ?? null;

		if (!is_int($value)) {
			$value = $stats[$this->resolveLegacyAbilityCode($abilityCode)] ?? null;
		}

		return is_int($value) ? $value : 10;
	}

	/**
	 * Возвращает legacy-код характеристики из ранних payload NPC.
	 */
	private function resolveLegacyAbilityCode(string $abilityCode): string
	{
		return match ($abilityCode) {
			'str' => 'strength',
			'dex' => 'dexterity',
			'con' => 'constitution',
			'int' => 'intelligence',
			'wis' => 'wisdom',
			'cha' => 'charisma',
			default => $abilityCode,
		};
	}

	/**
	 * Нормализует уровень актора.
	 */
	private function normalizeLevel(int $level): int
	{
		return max(1, min(20, $level));
	}
}
