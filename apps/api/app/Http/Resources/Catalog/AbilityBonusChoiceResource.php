<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Data\Catalog\AbilityBonusChoiceData;
use App\Domain\Catalog\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует вариант выбора бонусов характеристик в JSON.
 *
 * @mixin AbilityBonusChoiceData
 */
final class AbilityBonusChoiceResource extends JsonResource
{
	/**
	 * @return array{count:int,value:int,abilities:list<string>}
	 */
	public function toArray(Request $request): array
	{
		/** @var AbilityBonusChoiceData $choice */
		$choice = $this->resource;

		return [
			'count' => $choice->count,
			'value' => $choice->value,
			'abilities' => array_map(
				static function (string $abilityClass): string {
					/** @var class-string<Ability> $abilityClass */
					$ability = new $abilityClass;

					return $ability->getCode();
				},
				$choice->abilities,
			),
		];
	}
}
