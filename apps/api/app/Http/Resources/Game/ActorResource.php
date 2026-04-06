<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует persistent-актора в JSON.
 *
 * @mixin Actor
 */
final class ActorResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var Actor $actor */
		$actor = $this->resource;

		return [
			'id' => $actor->id,
			'gm_user_id' => $actor->gm_user_id,
			'kind' => $actor->kind,
			'name' => $actor->name,
			'description' => $actor->description,
			'race' => $actor->race,
			'character_class' => $actor->character_class,
			'level' => $actor->level,
			'movement_speed' => $actor->movement_speed,
			'base_health' => $actor->base_health,
			'health_current' => $actor->health_current,
			'health_max' => $actor->health_max,
			'stats' => $actor->stats,
			'inventory' => $actor->inventory,
			'image_path' => $actor->image_path,
			'image_url' => $actor->image_url,
			'meta' => $actor->meta,
			'created_at' => $actor->created_at?->toJSON(),
			'updated_at' => $actor->updated_at?->toJSON(),
			'game_master' => $this->whenLoaded('gameMaster', static function () use ($actor): ?array {
				/** @var User|null $gameMaster */
				$gameMaster = $actor->gameMaster;

				if ($gameMaster === null) {
					return null;
				}

				return [
					'id' => $gameMaster->id,
					'name' => $gameMaster->name,
					'email' => $gameMaster->email,
				];
			}),
		];
	}
}
