<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность льда.
 */
final class IceSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'ice';
	}

	public function image(): string
	{
		return 'ice.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Лед',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['slippery', 'cold'],
		];
	}
}
