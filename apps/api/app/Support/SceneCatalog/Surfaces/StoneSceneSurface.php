<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность камня.
 */
final class StoneSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'stone';
	}

	public function image(): string
	{
		return 'stone.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Камень',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['solid'],
		];
	}
}
