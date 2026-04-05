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

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Трава',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['natural', 'soft'],
		];
	}
}
