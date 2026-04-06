<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Actor\AbstractRace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует расу в JSON.
 *
 * @mixin AbstractRace
 */
final class ActorRaceResource extends JsonResource
{
	/**
	 * @return array<string,mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var AbstractRace $race */
		$race = $this->resource;

		return [
			'code' => $race->getCode(),
			'name' => $race->getName(),
			'description' => $race->getDescription(),
			'isActive' => $race->isActive(),
			'isPlayerSelectable' => true,
			'abilityBonuses' => AbilityBonusesResource::make($race->getAbilityBonuses())->resolve(),
			'abilityBonusChoices' => AbilityBonusChoiceResource::collection($race->getAbilityBonusChoices())->resolve(),
			'subraces' => ActorSubraceResource::collection($race->getActiveSubraces())->resolve(),
		];
	}
}
