<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность огня.
 */
final class FireSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'fire';
	}

	public function image(): string
	{
		return 'fire.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Огонь',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['hazard', 'damage'],
		];
	}
}
