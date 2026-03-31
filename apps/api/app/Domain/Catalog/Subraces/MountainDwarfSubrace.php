<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса горного дварфа.
 */
final class MountainDwarfSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'mountain-dwarf';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Горный дварф';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Тяжеловооруженные дварфы крепостей и кузниц.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик горного дварфа.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(strength: 2);
	}

}
