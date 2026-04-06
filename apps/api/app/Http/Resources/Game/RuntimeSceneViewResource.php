<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\RuntimeSceneViewData;
use App\Models\SceneObject;
use App\Models\SceneTemplateCell;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует подготовленное представление runtime-сцены в JSON.
 *
 * @mixin RuntimeSceneViewData
 */
final class RuntimeSceneViewResource extends JsonResource
{
	/**
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		/** @var RuntimeSceneViewData $view */
		$view = $this->resource;
		$sceneState = $view->sceneState;
		$sceneTemplate = $sceneState->sceneTemplate;
		$game = $sceneState->game;

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
			'game' => $game !== null ? [
				'id' => $game->id,
				'title' => $game->title,
				'status' => $game->status,
				'gm_user_id' => $game->gm_user_id,
				'active_scene_state_id' => $game->active_scene_state_id,
			] : null,
			'scene_template' => $sceneTemplate !== null ? [
				'id' => $sceneTemplate->id,
				'created_by' => $sceneTemplate->created_by,
				'name' => $sceneTemplate->name,
				'description' => $sceneTemplate->description,
				'width' => $sceneTemplate->width,
				'height' => $sceneTemplate->height,
				'status' => $sceneTemplate->status,
				'metadata' => $sceneTemplate->metadata,
				'created_at' => $sceneTemplate->created_at?->toJSON(),
				'updated_at' => $sceneTemplate->updated_at?->toJSON(),
				'cells' => $sceneTemplate->cells
					->map(static fn (SceneTemplateCell $cell): array => [
						'id' => $cell->id,
						'scene_template_id' => $cell->scene_template_id,
						'x' => $cell->x,
						'y' => $cell->y,
						'terrain_type' => $cell->terrain_type,
						'elevation' => $cell->elevation,
						'is_passable' => (bool) $cell->is_passable,
						'blocks_vision' => (bool) $cell->blocks_vision,
					])
					->values()
					->all(),
				'objects' => $sceneTemplate->objects
					->map(static fn (SceneObject $object): array => [
						'id' => $object->id,
						'scene_template_id' => $object->scene_template_id,
						'kind' => $object->kind,
						'name' => $object->name,
						'x' => $object->x,
						'y' => $object->y,
						'width' => $object->width,
						'height' => $object->height,
						'is_hidden' => (bool) $object->is_hidden,
						'is_interactive' => (bool) $object->is_interactive,
						'state' => $object->state,
						'trigger_rules' => $object->trigger_rules,
					])
					->values()
					->all(),
			] : null,
			'actor_instances' => ActorInstanceResource::collection($sceneState->actorInstances)->resolve($request),
			'item_drops' => RuntimeItemDropResource::collection($view->itemDrops)->resolve($request),
			'encounter' => $view->encounter !== null
				? RuntimeEncounterResource::make($view->encounter)->resolve($request)
				: null,
		];
	}
}
