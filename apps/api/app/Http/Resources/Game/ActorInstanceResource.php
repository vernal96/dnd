<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\ActorInstance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует runtime-актора в JSON.
 *
 * @mixin ActorInstance
 */
final class ActorInstanceResource extends JsonResource
{
	/**
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		/** @var ActorInstance $actorInstance */
		$actorInstance = $this->resource;

		return [
			'id' => $actorInstance->id,
			'game_id' => $actorInstance->game_id,
			'game_scene_state_id' => $actorInstance->game_scene_state_id,
			'player_character_id' => $actorInstance->player_character_id,
			'controlled_by_user_id' => $actorInstance->controlled_by_user_id,
			'kind' => $actorInstance->kind,
			'controller_type' => $actorInstance->controller_type,
			'name' => $actorInstance->name,
			'faction' => $actorInstance->faction,
			'social_state' => $actorInstance->social_state,
			'status' => $actorInstance->status,
			'x' => $actorInstance->x,
			'y' => $actorInstance->y,
			'initiative' => $actorInstance->initiative,
			'hp_current' => $actorInstance->hp_current,
			'hp_max' => $actorInstance->hp_max,
			'luck' => $actorInstance->luck,
			'is_hidden' => (bool) $actorInstance->is_hidden,
			'resources' => $actorInstance->resources,
			'temporary_effects' => $actorInstance->temporary_effects,
			'runtime_state' => $actorInstance->runtime_state,
			'image_url' => $actorInstance->image_url,
			'movement_speed' => $actorInstance->movement_speed,
		];
	}
}
