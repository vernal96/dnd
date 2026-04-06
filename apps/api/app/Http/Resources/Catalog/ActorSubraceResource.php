<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Actor\AbstractSubrace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует подрасу в JSON.
 *
 * @mixin AbstractSubrace
 */
final class ActorSubraceResource extends JsonResource
{
	/**
	 * @return array<string,mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var AbstractSubrace $subrace */
		$subrace = $this->resource;

		return [
			'code' => $subrace->getCode(),
			'name' => $subrace->getName(),
			'description' => $subrace->getDescription(),
			'isActive' => $subrace->isActive(),
			'abilityBonuses' => AbilityBonusesResource::make($subrace->getAbilityBonuses())->resolve(),
			'abilityBonusChoices' => AbilityBonusChoiceResource::collection($subrace->getAbilityBonusChoices())->resolve(),
		];
	}
}
