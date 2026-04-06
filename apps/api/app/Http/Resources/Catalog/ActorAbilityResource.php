<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Actor\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует характеристику персонажа в JSON.
 *
 * @mixin Ability
 */
final class ActorAbilityResource extends JsonResource
{
	/**
	 * @return array{code:string,name:string,description:?string,defaultValue:int}
	 */
	public function toArray(Request $request): array
	{
		/** @var Ability $ability */
		$ability = $this->resource;

		return [
			'code' => $ability->getCode(),
			'name' => $ability->getName(),
			'description' => $ability->getDescription(),
			'defaultValue' => 1,
		];
	}
}
