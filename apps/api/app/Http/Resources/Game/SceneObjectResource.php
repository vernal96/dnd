<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\SceneObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует authored-объект сцены в JSON.
 *
 * @mixin SceneObject
 */
final class SceneObjectResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var SceneObject $object */
		$object = $this->resource;

		return [
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
		];
	}
}
