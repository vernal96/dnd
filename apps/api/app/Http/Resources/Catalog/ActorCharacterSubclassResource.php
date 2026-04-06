<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\AbstractCharacterSubclass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует подкласс персонажа в JSON.
 *
 * @mixin AbstractCharacterSubclass
 */
final class ActorCharacterSubclassResource extends JsonResource
{
	/**
	 * @return array{code:string,name:string,description:?string,isActive:bool}
	 */
	public function toArray(Request $request): array
	{
		/** @var AbstractCharacterSubclass $subclass */
		$subclass = $this->resource;

		return [
			'code' => $subclass->getCode(),
			'name' => $subclass->getName(),
			'description' => $subclass->getDescription(),
			'isActive' => $subclass->isActive(),
		];
	}
}
