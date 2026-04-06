<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\Game;
use App\Models\GameSceneState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует игру в JSON.
 *
 * @mixin Game
 */
final class GameResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var Game $game */
		$game = $this->resource;

		return [
			'id' => $game->id,
			'title' => $game->title,
			'description' => $game->description,
			'gm_user_id' => $game->gm_user_id,
			'status' => $game->status,
			'active_scene_state_id' => $game->active_scene_state_id,
			'started_at' => $game->started_at?->toJSON(),
			'paused_at' => $game->paused_at?->toJSON(),
			'completed_at' => $game->completed_at?->toJSON(),
			'settings' => $game->settings,
			'created_at' => $game->created_at?->toJSON(),
			'updated_at' => $game->updated_at?->toJSON(),
			'members_count' => $game->members_count,
			'gm' => $this->whenLoaded(
				'gm',
				fn (): array => UserSummaryResource::make($game->gm)->resolve($request),
			),
			'members' => $this->whenLoaded(
				'members',
				fn (): array => GameMemberResource::collection($game->members)->resolve($request),
			),
			'invitations' => $this->whenLoaded(
				'invitations',
				fn (): array => GameInvitationResource::collection($game->invitations)->resolve($request),
			),
			'active_scene_state' => $this->whenLoaded('activeSceneState', function () use ($game, $request): ?array {
				/** @var GameSceneState|null $sceneState */
				$sceneState = $game->activeSceneState;

				if ($sceneState === null) {
					return null;
				}

				return [
					'id' => $sceneState->id,
					'game_id' => $sceneState->game_id,
					'scene_template_id' => $sceneState->scene_template_id,
					'status' => $sceneState->status,
					'version' => $sceneState->version,
					'loaded_at' => $sceneState->loaded_at?->toJSON(),
					'scene_template' => $sceneState->relationLoaded('sceneTemplate') && $sceneState->sceneTemplate !== null
						? SceneTemplateResource::make($sceneState->sceneTemplate)->resolve($request)
						: null,
				];
			}),
			'scene_states' => $this->whenLoaded('sceneStates', function () use ($game, $request): array {
				return $game->sceneStates
					->map(static function (GameSceneState $sceneState) use ($request): array {
						return [
							'id' => $sceneState->id,
							'game_id' => $sceneState->game_id,
							'scene_template_id' => $sceneState->scene_template_id,
							'status' => $sceneState->status,
							'version' => $sceneState->version,
							'created_at' => $sceneState->created_at?->toJSON(),
							'updated_at' => $sceneState->updated_at?->toJSON(),
							'scene_template' => $sceneState->relationLoaded('sceneTemplate') && $sceneState->sceneTemplate !== null
								? SceneTemplateResource::make($sceneState->sceneTemplate)->resolve($request)
								: null,
						];
					})
					->values()
					->all();
			}),
		];
	}
}
