<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность яда.
 */
final class PoisonSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'poison';
	}

	public function image(): string
	{
		return 'poison.png';
	}

	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
			'name' => 'Яд',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['hazard', 'toxic'],
		];
	}
}
