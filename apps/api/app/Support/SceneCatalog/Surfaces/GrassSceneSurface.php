<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность травы.
 */
final class GrassSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'grass';
	}

	public function image(): string
	{
		return 'grass.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Трава',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['natural', 'soft'],
		];
	}
}
