<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Перечисляет уровни персонажа и пороги опыта.
 */
enum CharacterLevel: int
{
	case Level1 = 1;
	case Level2 = 2;
	case Level3 = 3;
	case Level4 = 4;
	case Level5 = 5;
	case Level6 = 6;
	case Level7 = 7;
	case Level8 = 8;
	case Level9 = 9;
	case Level10 = 10;
	case Level11 = 11;
	case Level12 = 12;
	case Level13 = 13;
	case Level14 = 14;
	case Level15 = 15;
	case Level16 = 16;
	case Level17 = 17;
	case Level18 = 18;
	case Level19 = 19;
	case Level20 = 20;

	/**
	 * Возвращает максимальный поддерживаемый уровень.
	 */
	public static function maxLevel(): int
	{
		return self::Level20->value;
	}

	/**
	 * Возвращает уровень для количества опыта.
	 */
	public static function fromExperience(int $experience): self
	{
		$level = 1;
		$remainingExperience = max(0, $experience);

		for ($nextLevel = 2; $nextLevel <= self::maxLevel(); $nextLevel++) {
			$requiredExperience = self::experienceForTransitionTo($nextLevel);

			if ($remainingExperience < $requiredExperience) {
				break;
			}

			$remainingExperience -= $requiredExperience;
			$level = $nextLevel;
		}

		return self::from($level);
	}

	/**
	 * Возвращает опыт, нужный для перехода с предыдущего уровня на указанный.
	 */
	public static function experienceForTransitionTo(int $level): int
	{
		if ($level <= self::Level1->value) {
			return 0;
		}

		return ($level - 1) * 1000;
	}

	/**
	 * Возвращает суммарный опыт, нужный для достижения этого уровня.
	 */
	public function requiredTotalExperience(): int
	{
		$totalExperience = 0;

		for ($level = 2; $level <= $this->value; $level++) {
			$totalExperience += self::experienceForTransitionTo($level);
		}

		return $totalExperience;
	}
}
