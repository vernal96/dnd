<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\Game;
use App\Models\GameSceneState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует authored runtime-state сцены в JSON.
 *
 * @mixin GameSceneState
 */
final class GameSceneStateResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var GameSceneState $sceneState */
		$sceneState = $this->resource;

		return [
			'id' => $sceneState->id,
			'game_id' => $sceneState->game_id,
			'scene_template_id' => $sceneState->scene_template_id,
			'status' => $sceneState->status,
			'version' => $sceneState->version,
			'grid_state' => $sceneState->grid_state,
			'objects_state' => $sceneState->objects_state,
			'visibility_state' => $sceneState->visibility_state,
			'effects_state' => $sceneState->effects_state,
			'runtime_state' => $sceneState->runtime_state,
			'loaded_at' => $sceneState->loaded_at?->toJSON(),
			'resolved_at' => $sceneState->resolved_at?->toJSON(),
			'created_at' => $sceneState->created_at?->toJSON(),
			'updated_at' => $sceneState->updated_at?->toJSON(),
			'item_drops' => $sceneState->item_drops,
			'game' => $this->whenLoaded('game', static function () use ($sceneState): ?array {
				/** @var Game|null $game */
				$game = $sceneState->game;

				if ($game === null) {
					return null;
				}

				return [
					'id' => $game->id,
					'title' => $game->title,
					'status' => $game->status,
					'gm_user_id' => $game->gm_user_id,
					'active_scene_state_id' => $game->active_scene_state_id,
				];
			}),
			'scene_template' => $this->whenLoaded(
				'sceneTemplate',
				fn (): array => SceneTemplateResource::make($sceneState->sceneTemplate)->resolve($request),
			),
			'actor_instances' => $this->whenLoaded(
				'actorInstances',
				fn (): array => ActorInstanceResource::collection($sceneState->actorInstances)->resolve($request),
			),
			'encounters' => $this->whenLoaded(
				'encounters',
				fn (): array => EncounterResource::collection($sceneState->encounters)->resolve($request),
			),
		];
	}
}
