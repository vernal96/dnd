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

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Вода',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['liquid', 'hazard'],
		];
	}
}
