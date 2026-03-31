<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Domain\Catalog\AbstractRace;

/**
 * Сущность расы полуорка.
 */
final class HalfOrcRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'half-orc';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Полуорк';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Сильные и стойкие воины, привыкшие выживать между суровостью и предубеждением.';
	}

}
