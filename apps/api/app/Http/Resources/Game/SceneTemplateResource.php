<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует authored-шаблон сцены в JSON.
 *
 * @mixin SceneTemplate
 */
final class SceneTemplateResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var SceneTemplate $sceneTemplate */
		$sceneTemplate = $this->resource;

		return [
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
			'cells_count' => $this->whenCounted('cells'),
			'objects_count' => $this->whenCounted('objects'),
			'scene_states_count' => $this->whenCounted('sceneStates'),
			'author' => $this->whenLoaded('author', static function () use ($sceneTemplate): ?array {
				/** @var User|null $author */
				$author = $sceneTemplate->author;

				if ($author === null) {
					return null;
				}

				return [
					'id' => $author->id,
					'name' => $author->name,
					'email' => $author->email,
				];
			}),
			'cells' => $this->whenLoaded(
				'cells',
				fn (): array => SceneTemplateCellResource::collection($sceneTemplate->cells)->resolve($request),
			),
			'objects' => $this->whenLoaded(
				'objects',
				fn (): array => SceneObjectResource::collection($sceneTemplate->objects)->resolve($request),
			),
			'actor_placements' => $this->whenLoaded(
				'actorPlacements',
				fn (): array => SceneActorPlacementResource::collection($sceneTemplate->actorPlacements)->resolve($request),
			),
		];
	}
}
