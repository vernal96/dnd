<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\SceneActorPlacement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует authored-размещение актора на сцене в JSON.
 *
 * @mixin SceneActorPlacement
 */
final class SceneActorPlacementResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var SceneActorPlacement $placement */
		$placement = $this->resource;

		return [
			'id' => $placement->id,
			'scene_template_id' => $placement->scene_template_id,
			'actor_id' => $placement->actor_id,
			'x' => $placement->x,
			'y' => $placement->y,
			'actor' => $this->whenLoaded('actor', fn (): array => ActorResource::make($placement->actor)->resolve($request)),
		];
	}
}
