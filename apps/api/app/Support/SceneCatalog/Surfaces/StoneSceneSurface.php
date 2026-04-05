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

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Камень',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['solid'],
		];
	}
}
