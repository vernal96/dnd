<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\SceneTemplateCell;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует authored-клетку сцены в JSON.
 *
 * @mixin SceneTemplateCell
 */
final class SceneTemplateCellResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var SceneTemplateCell $cell */
		$cell = $this->resource;

		return [
			'id' => $cell->id,
			'scene_template_id' => $cell->scene_template_id,
			'x' => $cell->x,
			'y' => $cell->y,
			'terrain_type' => $cell->terrain_type,
			'elevation' => $cell->elevation,
			'is_passable' => (bool) $cell->is_passable,
			'blocks_vision' => (bool) $cell->blocks_vision,
			'props' => $cell->props,
		];
	}
}
