<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность воды.
 */
final class WaterSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'water';
	}

	public function image(): string
	{
		return 'water.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Вода',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['liquid', 'hazard'],
		];
	}
}
