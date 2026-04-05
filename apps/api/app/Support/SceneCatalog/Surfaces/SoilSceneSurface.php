<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность земли.
 */
final class SoilSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'soil';
	}

	public function image(): string
	{
		return 'soil.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Земля',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['natural'],
		];
	}
}
