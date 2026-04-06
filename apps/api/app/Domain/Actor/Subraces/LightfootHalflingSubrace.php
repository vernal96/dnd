<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Actor\AbstractSubrace;

/**
 * Подраса легконогого полурослика.
 */
final class LightfootHalflingSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'lightfoot-halfling';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Легконогий';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Общительные и проворные полурослики, склонные к путешествиям и дипломатии.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик легконогого полурослика.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(charisma: 1);
	}

}
