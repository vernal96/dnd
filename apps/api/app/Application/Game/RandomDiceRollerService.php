<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Actor\Dice;
use App\Domain\Actor\LuckScale;

/**
 * Выполняет псевдослучайные броски кубиков с учетом удачи актора.
 */
class RandomDiceRollerService
{
	/**
	 * Выполняет один бросок указанного кубика.
	 */
	public function roll(Dice $dice, LuckScale $luckScale): int
	{
		$firstRoll = random_int(1, $dice->value);

		if ($luckScale === LuckScale::Normal) {
			return $firstRoll;
		}

		$secondRoll = random_int(1, $dice->value);

		return $luckScale === LuckScale::Bad
			? min($firstRoll, $secondRoll)
			: max($firstRoll, $secondRoll);
	}
}
