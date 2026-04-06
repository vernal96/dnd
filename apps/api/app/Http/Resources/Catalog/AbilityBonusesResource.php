<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Data\Catalog\AbilityBonusesData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует бонусы характеристик в JSON.
 *
 * @mixin AbilityBonusesData
 */
final class AbilityBonusesResource extends JsonResource
{
	/**
	 * @return array{str:int,dex:int,con:int,int:int,wis:int,cha:int}
	 */
	public function toArray(Request $request): array
	{
		/** @var AbilityBonusesData $bonuses */
		$bonuses = $this->resource;

		return [
			'str' => $bonuses->strength,
			'dex' => $bonuses->dexterity,
			'con' => $bonuses->constitution,
			'int' => $bonuses->intelligence,
			'wis' => $bonuses->wisdom,
			'cha' => $bonuses->charisma,
		];
	}
}
