<?php

declare(strict_types=1);

namespace App\Domain\Actor\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractRace;

/**
 * Сущность расы тифлинга.
 */
final class TieflingRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'tiefling';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Тифлинг';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Носители инфернального наследия, сочетающие внутреннюю силу, харизму и печать чуждости.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик тифлинга.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(
			intelligence: 1,
			charisma: 2,
		);
	}

}
