<?php

declare(strict_types=1);

namespace App\Domain\Actor\Races;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractRace;
use App\Domain\Actor\AbstractSubrace;
use App\Domain\Actor\Subraces\LightfootHalflingSubrace;
use App\Domain\Actor\Subraces\StoutHalflingSubrace;

/**
 * Сущность расы полурослика.
 */
final class HalflingRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'halfling';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Полурослик';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Небольшой, ловкий и удачливый народ, ценящий дом, дорогу и простые радости.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик полурослика.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(dexterity: 2);
	}

	/**
	 * Возвращает подрасы полурослика.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [
			new LightfootHalflingSubrace,
			new StoutHalflingSubrace,
		];
	}
}
