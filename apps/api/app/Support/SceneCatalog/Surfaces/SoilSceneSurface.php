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

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Земля',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['natural'],
		];
	}
}
