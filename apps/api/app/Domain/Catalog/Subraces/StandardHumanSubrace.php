<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Data\Catalog\AbilityBonusesData;
use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса обычного человека.
 */
final class StandardHumanSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'standard-human';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Обычный человек';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Сбалансированный представитель человечества без выраженной ранней специализации.';
	}

	/**
	 * Возвращает фиксированные бонусы характеристик обычного человека.
	 *
	 */
	public function getAbilityBonuses(): AbilityBonusesData
	{
		return new AbilityBonusesData(
			strength: 1,
			dexterity: 1,
			constitution: 1,
			intelligence: 1,
			wisdom: 1,
			charisma: 1,
		);
	}

}
