<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\RuntimeItemDropData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует runtime-дроп предмета в JSON.
 *
 * @mixin RuntimeItemDropData
 */
final class RuntimeItemDropResource extends JsonResource
{
	/**
	 * @return array{id: string, item_code: string, name: ?string, quantity: int, x: int, y: int, image_url: ?string}
	 */
	public function toArray(Request $request): array
	{
		/** @var RuntimeItemDropData $itemDrop */
		$itemDrop = $this->resource;

		return [
			'id' => $itemDrop->id,
			'item_code' => $itemDrop->itemCode,
			'name' => $itemDrop->name,
			'quantity' => $itemDrop->quantity,
			'x' => $itemDrop->x,
			'y' => $itemDrop->y,
			'image_url' => $itemDrop->imageUrl,
		];
	}
}
