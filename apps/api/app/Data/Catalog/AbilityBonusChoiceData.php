<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Catalog\Ability;

/**
 * Хранит один вариант выбора бонусов характеристик.
 */
final readonly class AbilityBonusChoiceData
{
	/**
	 * Создает DTO варианта выбора бонусов характеристик.
	 *
	 * @param list<class-string<Ability>> $abilities
	 */
	public function __construct(
		public int   $count,
		public int   $value,
		public array $abilities,
	)
	{
	}

}
